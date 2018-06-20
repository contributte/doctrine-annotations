<?php declare(strict_types = 1);

namespace Tests\Nettrine\Annotations\Cases\DI;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		//Required services and params
		$compiler->addConfig(['parameters' => ['tempDir' => TEMP_DIR]]);
		$compiler->addExtension('annotations', new AnnotationsExtension());
	}, '1');

	/** @var Container $container */
	$container = new $class();
	Assert::type(CachedReader::class, $container->getByType(Reader::class));
	Assert::type(FilesystemCache::class, $container->getService('annotations.cache'));
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		//Required services and params
		$compiler->addConfig(['parameters' => ['tempDir' => TEMP_DIR]]);
		$compiler->addExtension('annotations', new AnnotationsExtension());
		$compiler->loadConfig(FileMock::create('
		services:
			mycache: Doctrine\Common\Cache\ApcuCache
		
		annotations:
			cache: @mycache
		', 'neon'));
	}, '2');

	/** @var Container $container */
	$container = new $class();
	Assert::type(CachedReader::class, $container->getByType(Reader::class));
	Assert::type(ApcuCache::class, $container->getService('annotations.cache'));
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		//Required services and params
		$compiler->addConfig(['parameters' => ['tempDir' => TEMP_DIR]]);
		$compiler->addExtension('annotations', new AnnotationsExtension());
		$compiler->loadConfig(FileMock::create('
		annotations:
			cache: off
		', 'neon'));
	}, '3');

	/** @var Container $container */
	$container = new $class();
	Assert::type(CachedReader::class, $container->getByType(Reader::class));
	Assert::type(ArrayCache::class, $container->getService('annotations.cache'));
});
