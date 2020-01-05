<?php declare(strict_types = 1);

namespace Nettrine\Annotations\DI;

use Contributte\DI\Extension\CompilerExtension;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
class AnnotationsExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'debug' => Expect::bool(false),
			'ignore' => Expect::listOf('string')->default([
				'persistent',
				'serializationVersion',
			]),
			'cache' => Expect::anyOf(
				Expect::string(),
				Expect::array(),
				Expect::type(Statement::class)
			)->nullable(),
		]);
	}

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$readerDefinition = $builder->addDefinition($this->prefix('delegatedReader'))
			->setFactory(AnnotationReader::class)
			->setAutowired(false);

		foreach ($config->ignore as $annotationName) {
			$readerDefinition->addSetup('addGlobalIgnoredName', [$annotationName]);
			AnnotationReader::addGlobalIgnoredName($annotationName);
		}

		if ($config->cache !== null) {
			$cacheName = $this->prefix('cache');
			$cacheDefinition = $this->getHelper()->getDefinitionFromConfig($config->cache, $cacheName);

			// If service is extension specific, then disable autowiring
			if ($cacheDefinition instanceof Definition && $cacheDefinition->getName() === $cacheName) {
				$cacheDefinition->setAutowired(false);
			}
		} else {
			$cacheDefinition = '@' . Cache::class;
		}

		$builder->addDefinition($this->prefix('reader'))
			->setType(Reader::class)
			->setFactory(CachedReader::class, [
				$readerDefinition,
				$cacheDefinition,
				$config->debug,
			]);

		AnnotationRegistry::registerUniqueLoader('class_exists');
	}

	public function afterCompile(ClassType $classType): void
	{
		$initialize = $classType->getMethod('initialize');
		$original = (string) $initialize->getBody();
		$initialize->setBody('?::registerUniqueLoader("class_exists");' . "\n", [new PhpLiteral(AnnotationRegistry::class)]);
		$initialize->addBody($original);
	}

}
