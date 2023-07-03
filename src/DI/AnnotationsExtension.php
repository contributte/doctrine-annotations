<?php declare(strict_types = 1);

namespace Nettrine\Annotations\DI;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
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
			$cacheDefinition = $builder->addDefinition($this->prefix('cache'));
			$cacheDefinition->setFactory($config->cache)
				->setAutowired(false);
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
		$original = $initialize->getBody();
		$initialize->setBody('?::registerUniqueLoader("class_exists");' . "\n", [new Literal(AnnotationRegistry::class)]);
		$initialize->addBody($original);
	}

}
