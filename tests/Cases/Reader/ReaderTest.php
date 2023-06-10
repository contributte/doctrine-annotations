<?php declare(strict_types = 1);

namespace Tests\Cases\Reader;

use Contributte\Tester\Environment;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Common\Annotations\Reader;
use Nette\DI\Compiler;
use Nettrine\Annotations\DI\AnnotationsExtension;
use ReflectionClass;
use Tester\Assert;
use Tester\TestCase;
use Tests\Fixtures\SampleAnnotation;
use Tests\Fixtures\SampleClass;

require __DIR__ . '/../../bootstrap.php';

final class ReaderTest extends TestCase
{

	public function testIgnoreAnnotations(): void
	{
		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('annotations', new AnnotationsExtension());
				$compiler->addConfig(['parameters' => ['tempDir' => Environment::getTestDir()]]);
				$compiler->addConfig(Neonkit::load('
					annotations:
						cache: Doctrine\Common\Cache\FilesystemCache(%tempDir%/nettrine.annotations)
						ignore:
							- ignoredAnnotation
				'));
				$compiler->addDependencies([__FILE__]);
			})
			->build();

		$reader = $container->getByType(Reader::class);
		Assert::type(Reader::class, $reader);

		$annotations = $reader->getClassAnnotations(new ReflectionClass(SampleClass::class));

		Assert::notEqual(0, count($annotations));
		Assert::type(SampleAnnotation::class, $annotations[0]);
		Assert::equal('foo', $annotations[0]->getValue());
	}

}

(new ReaderTest())->run();
