<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Exception;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\InvalidStateException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Tester\Assert;
use Tester\TestCase;
use Tests\Toolkit\NeonLoader;
use Tests\Toolkit\Tests;

require __DIR__ . '/../../bootstrap.php';

final class AnnotationsExtensionTest extends TestCase
{

	public function testAutowiredCache(): void
	{
		$loader = new ContainerLoader(Tests::TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Tests::TEMP_PATH,
				],
			]);
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		Assert::type(CachedReader::class, $container->getByType(Reader::class));
		Assert::type(PhpFileCache::class, $container->getService('cache.driver'));
	}

	public function testProvidedCache(): void
	{
		$loader = new ContainerLoader(Tests::TEMP_PATH, true);
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

		Assert::type(CachedReader::class, $container->getByType(Reader::class));
		Assert::type(ApcuCache::class, $container->getService('annotations.cache'));
		Assert::null($container->getByType(Cache::class, false));
	}

	public function testNoCache(): void
	{
		Assert::type(new Exception(), new InvalidStateException());

		Assert::exception(function (): void {
			$loader = new ContainerLoader(Tests::TEMP_PATH, true);
			$class = $loader->load(static function (Compiler $compiler): void {
				$compiler->addExtension('annotations', new AnnotationsExtension());
				$compiler->addDependencies([__FILE__]);
			}, __METHOD__);

			$container = new $class();
			Assert::true($container instanceof Container);
		}, InvalidStateException::class, "Service 'annotations.reader' (type of Doctrine\Common\Annotations\Reader): Service of type Doctrine\Common\Cache\Cache not found. Did you add it to configuration file?");
	}

}

(new AnnotationsExtensionTest())->run();
