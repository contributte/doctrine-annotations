<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\InvalidStateException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Tests\Toolkit\NeonLoader;
use Tests\Toolkit\TestCase;

final class AnnotationsExtensionTest extends TestCase
{

	public function testAutowiredCache(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addConfig(NeonLoader::load('
			services:
				mycache: Doctrine\Common\Cache\ApcuCache
		'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		$this->assertInstanceOf(CachedReader::class, $container->getByType(Reader::class));
		$this->assertInstanceOf(ApcuCache::class, $container->getService('mycache'));
	}

	public function testProvidedCache(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addConfig(NeonLoader::load('
			annotations:
				cache: Doctrine\Common\Cache\ApcuCache
		'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		$this->assertInstanceOf(CachedReader::class, $container->getByType(Reader::class));
		$this->assertInstanceOf(ApcuCache::class, $container->getService('annotations.cache'));
	}

	public function testNoCache(): void
	{
		$this->expectException(InvalidStateException::class);
		$this->expectExceptionMessage('An autowired service of type \'Doctrine\Common\Cache\Cache\' not found. Please register it or provide \'annotations > cache\' configuration.');

		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);
	}

}
