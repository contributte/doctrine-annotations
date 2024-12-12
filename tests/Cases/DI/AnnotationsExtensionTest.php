<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Nette\DI\Compiler;
use Nette\InvalidStateException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
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
				$compiler->addConfig([
					'parameters' => [
						'tempDir' => Environment::getTestDir(),
					],
					'annotations' => [
						'cache' => PhpFilesAdapter::class, // Use PSR-6 cache
					],
				]);
				$compiler->addDependencies([__FILE__]);
			})
			->build();

		Assert::type(PsrCachedReader::class, $container->getByType(Reader::class));
		Assert::type(PhpFilesAdapter::class, $container->getService('annotations.cache'));
	}

	public function testProvidedCache(): void
	{
		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('annotations', new AnnotationsExtension());
				$compiler->addConfig(Neonkit::load('
                    annotations:
                        cache: Symfony\Component\Cache\Adapter\ArrayAdapter
                '));
				$compiler->addDependencies([__FILE__]);
			})
			->build();

		Assert::type(PsrCachedReader::class, $container->getByType(Reader::class));
		Assert::type(ArrayAdapter::class, $container->getService('annotations.cache'));
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
