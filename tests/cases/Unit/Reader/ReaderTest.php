<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Annotations\DI\AnnotationsExtension;
use ReflectionClass;
use Tests\Fixtures\SampleAnnotation;
use Tests\Fixtures\SampleClass;
use Tests\Toolkit\NeonLoader;
use Tests\Toolkit\TestCase;

final class ReaderTest extends TestCase
{

	public function testIgnoreAnnotations(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addConfig(['parameters' => ['tempDir' => TEMP_PATH]]);
			$compiler->addConfig(NeonLoader::load('
			annotations:
				ignore:
					- ignoredAnnotation
		', 'neon'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();

		/** @var AnnotationReader $reader */
		$reader = $container->getByType(Reader::class);

		$annotations = $reader->getClassAnnotations(new ReflectionClass(SampleClass::class));

		$this->assertNotCount(0, $annotations);
		$this->assertInstanceOf(SampleAnnotation::class, $annotations[0]);
		$this->assertEquals('foo', $annotations[0]->getValue());
	}

}
