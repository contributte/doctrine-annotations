<?php declare(strict_types = 1);

namespace Nettrine\Annotations\DI;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\DI\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;

class AnnotationsExtension extends CompilerExtension
{

	/** @var mixed[] */
	public $defaults = [
		'debug' => false,
		'ignore' => [],
		'cache' => FilesystemCache::class,
	];

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$reader = $builder->addDefinition($this->prefix('delegatedReader'))
			->setFactory(AnnotationReader::class)
			->setAutowired(false);

		foreach ((array) $config['ignore'] as $annotationName) {
			$reader->addSetup('addGlobalIgnoredName', [$annotationName]);
			AnnotationReader::addGlobalIgnoredName($annotationName);
		}

		$this->loadCacheConfiguration();

		$builder->addDefinition($this->prefix('reader'))
			->setType(Reader::class)
			->setFactory(CachedReader::class, [
				$this->prefix('@delegatedReader'),
				$this->prefix('@cache'),
				$config['debug'],
			]);

		AnnotationRegistry::registerUniqueLoader('class_exists');
	}

	protected function loadCacheConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (is_string($config['cache'])) {
			// FilesystemCache needs extra configuration (paths)
			if ($config['cache'] === FilesystemCache::class) {
				$path = Helpers::expand('%tempDir%/cache/Doctrine.Annotations', $builder->parameters);
				$builder->addDefinition($this->prefix('cache'))
					->setFactory($config['cache'], [$path])
					->setAutowired(false);
			} else {
				$builder->addDefinition($this->prefix('cache'))
					->setFactory($config['cache'])
					->setAutowired(false);
			}
		} elseif ($config['cache'] instanceof Statement) {
			// Filled by other service
			$builder->addDefinition($this->prefix('cache'))
				->setFactory($config['cache'])
				->setAutowired(false);
		} elseif ($config['cache'] === null || $config['cache'] === false) {
			// No cache (memory only)
			$builder->addDefinition($this->prefix('cache'))
				->setFactory(ArrayCache::class)
				->setAutowired(false);
		}
	}

	public function afterCompile(ClassType $classType): void
	{
		$initialize = $classType->getMethod('initialize');
		$original = (string) $initialize->getBody();
		$initialize->setBody('?::registerUniqueLoader("class_exists");' . "\n", [new PhpLiteral(AnnotationRegistry::class)]);
		$initialize->addBody($original);
	}

}
