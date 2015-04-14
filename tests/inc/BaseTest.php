<?php

use Tester\TestCase ;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Markette\Gopay\Extension ;


class BaseTest extends TestCase
{
	/** @return Container */
	protected function createContainer($file)
	{
		$loader = new ContainerLoader(TEMP_DIR);
		$key = 'key';
		$className = $loader->load($key, function (Compiler $compiler) use($file){
			$compiler->addExtension('gopay', new Extension());
			$compiler->loadConfig(__DIR__ . '/../files/' . $file );
		});

		return new $className;
	}
}
