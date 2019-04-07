<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Tests\Toolkit\NeonLoader;
use Tests\Toolkit\TestCase;

final class AnnotationsExtensionTest extends TestCase
{

	public function testRegister(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addConfig(['parameters' => ['tempDir' => TEMP_PATH]]);
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();

		$this->assertInstanceOf(CachedReader::class, $container->getByType(Reader::class));
		$this->assertInstanceOf(FilesystemCache::class, $container->getService('annotations.cache'));
	}

	public function testProvideCache(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addConfig(['parameters' => ['tempDir' => TEMP_PATH]]);
			$compiler->addConfig(NeonLoader::load('
			services:
				mycache: Doctrine\Common\Cache\ApcuCache
			
			annotations:
				cache: @mycache
		', 'neon'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();

		$this->assertInstanceOf(CachedReader::class, $container->getByType(Reader::class));
		$this->assertInstanceOf(ApcuCache::class, $container->getService('annotations.cache'));
	}

	public function testDisableCache(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addConfig(['parameters' => ['tempDir' => TEMP_PATH]]);
			$compiler->addConfig(NeonLoader::load('
			annotations:
				cache: null
		', 'neon'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();
		$this->assertInstanceOf(CachedReader::class, $container->getByType(Reader::class));
		$this->assertInstanceOf(ArrayCache::class, $container->getService('annotations.cache'));
	}

}
