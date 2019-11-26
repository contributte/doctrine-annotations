<?php declare(strict_types = 1);

namespace Tests\Nettrine\Annotations\Unit\DI;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\InvalidStateException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Tests\Nettrine\Annotations\Toolkit\NeonLoader;
use Tests\Nettrine\Annotations\Toolkit\TestCase;

final class AnnotationsExtensionTest extends TestCase
{

	public function testAutowiredCache(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => TEMP_PATH,
				],
			]);
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		$this->assertInstanceOf(CachedReader::class, $container->getByType(Reader::class));
		$this->assertInstanceOf(PhpFileCache::class, $container->getService('cache.driver'));
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
		$this->assertNull($container->getByType(Cache::class, false));
	}

	public function testNoCache(): void
	{
		$this->expectException(InvalidStateException::class);
		$this->expectExceptionMessage('Service \'annotations.reader\' (type of Doctrine\Common\Annotations\Reader): Service of type \'Doctrine\Common\Cache\Cache\' not found.');

		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);
	}

}
