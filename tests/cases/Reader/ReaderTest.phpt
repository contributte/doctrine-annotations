<?php declare(strict_types = 1);

namespace Tests\Nettrine\Annotations\Cases\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Annotations\DI\AnnotationsExtension;
use ReflectionClass;
use Tester\Assert;
use Tester\FileMock;
use Tester\TestCase;
use Tests\Nettrine\Annotations\Fixtures\SampleAnnotation;
use Tests\Nettrine\Annotations\Fixtures\SampleClass;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class ReaderTest extends TestCase
{

	public function testClassAnnotations(): void
	{
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addConfig(['parameters' => ['tempDir' => TEMP_DIR]]);
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->loadConfig(FileMock::create('
			annotations:
				ignore:
					- ignoredAnnotation
		', 'neon'));
		}, '1a');
		/** @var Container $container */
		$container = new $class();

		/** @var AnnotationReader $reader */
		$reader = $container->getByType(Reader::class);

		$annotations = $reader->getClassAnnotations(new ReflectionClass(SampleClass::class));

		Assert::notEqual(0, count($annotations));
		Assert::type(SampleAnnotation::class, $annotations[0]);
		Assert::equal('foo', $annotations[0]->getValue());
	}

}

(new ReaderTest())->run();
