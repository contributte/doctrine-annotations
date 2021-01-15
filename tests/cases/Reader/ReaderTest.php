<?php declare(strict_types = 1);

namespace Tests\Cases\Reader;

use Doctrine\Common\Annotations\Reader;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Annotations\DI\AnnotationsExtension;
use ReflectionClass;
use Tester\Assert;
use Tester\TestCase;
use Tests\Fixtures\SampleAnnotation;
use Tests\Fixtures\SampleClass;
use Tests\Toolkit\NeonLoader;
use Tests\Toolkit\Tests;

require __DIR__ . '/../../bootstrap.php';

final class ReaderTest extends TestCase
{

	public function testIgnoreAnnotations(): void
	{
		$loader = new ContainerLoader(Tests::TEMP_PATH, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addConfig(['parameters' => ['tempDir' => Tests::TEMP_PATH]]);
			$compiler->addConfig(NeonLoader::load('
			annotations:
				cache: Doctrine\Common\Cache\FilesystemCache(%tempDir%/nettrine.annotations)
				ignore:
					- ignoredAnnotation
		'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		Assert::true($container instanceof Container);

		$reader = $container->getByType(Reader::class);
		Assert::true($reader instanceof Reader);

		$annotations = $reader->getClassAnnotations(new ReflectionClass(SampleClass::class));

		Assert::notEqual(0, count($annotations));
		Assert::type(SampleAnnotation::class, $annotations[0]);
		Assert::equal('foo', $annotations[0]->getValue());
	}

}

(new ReaderTest())->run();
