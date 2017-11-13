<?php

namespace Tests\Reader;

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
use Tests\Reader\Files\SampleAnnotation;
use Tests\Reader\Files\SampleClass;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class ReaderTest extends TestCase
{

	/**
	 * @return void
	 */
	public function testClassAnnotations()
	{
		$loader = new ContainerLoader(TEMP_DIR, TRUE);
		$class = $loader->load(function (Compiler $compiler) {
			$compiler->addConfig(['parameters' => ['tempDir' => TEMP_DIR]]);
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->loadConfig(FileMock::create('
			annotations:
				ignore:
					- ignoredAnnotation
		', 'neon'));
		}, '1a');
		/** @var Container $container */
		$container = new $class;

		/** @var AnnotationReader $reader */
		$reader = $container->getByType(Reader::class);

		$annotations = $reader->getClassAnnotations(new ReflectionClass(SampleClass::class));

		Assert::notEqual(0, count($annotations));
		Assert::type(SampleAnnotation::class, $annotations[0]);
		Assert::equal('foo', $annotations[0]->getValue());
	}

}

(new ReaderTest())->run();
