<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Nette\DI\Compiler;
use Nette\InvalidStateException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class AnnotationsExtensionTest extends TestCase
{

	public function testAutowiredCache(): void
	{
		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('annotations', new AnnotationsExtension());
				$compiler->addExtension('cache', new CacheExtension());
				$compiler->addConfig([
					'parameters' => [
						'tempDir' => Environment::getTestDir(),
					],
				]);
				$compiler->addDependencies([__FILE__]);
			})
			->build();

		Assert::type(CachedReader::class, $container->getByType(Reader::class));
		Assert::type(PhpFileCache::class, $container->getService('cache.driver'));
	}

	public function testProvidedCache(): void
	{
		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('annotations', new AnnotationsExtension());
				$compiler->addConfig(Neonkit::load('
					annotations:
						cache: Doctrine\Common\Cache\ApcuCache
				'));
				$compiler->addDependencies([__FILE__]);
			})
			->build();

		Assert::type(CachedReader::class, $container->getByType(Reader::class));
		Assert::type(ApcuCache::class, $container->getService('annotations.cache'));
		Assert::null($container->getByType(Cache::class, false));
	}

	public function testNoCache(): void
	{
		Assert::exception(function (): void {
			ContainerBuilder::of()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addExtension('annotations', new AnnotationsExtension());
					$compiler->addDependencies([__FILE__]);
				})
				->build();
		}, InvalidStateException::class, "Service 'annotations.reader' (type of Doctrine\Common\Annotations\Reader): Service of type Doctrine\Common\Cache\Cache not found. Did you add it to configuration file?");
	}

}

(new AnnotationsExtensionTest())->run();
