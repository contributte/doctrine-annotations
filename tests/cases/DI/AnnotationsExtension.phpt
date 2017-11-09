<?php

/**
 * Test: DI\AnnotationsExtension
 */

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		//Required services and params
		$compiler->loadConfig(FileMock::create('
			parameters:
				tempDir: "/srv/temp"
		', 'neon'));
		$compiler->addExtension('annotations', new AnnotationsExtension());
	}, '1a');

	/** @var Container $container */
	$container = new $class;

	Assert::type(AnnotationReader::class, $container->getByType(Reader::class));
});
