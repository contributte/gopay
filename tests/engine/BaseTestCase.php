<?php declare(strict_types = 1);

namespace Tests\Engine;

use Markette\Gopay\DI\Extension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use RuntimeException;
use Tester\TestCase;

abstract class BaseTestCase extends TestCase
{

	/**
	 * @param mixed $config
	 * @return Container
	 */
	protected function createContainer($config = null)
	{
		$loader = new ContainerLoader(TEMP_DIR);
		$className = $loader->load(function (Compiler $compiler) use ($config) {
			$compiler->addExtension('gopay', new Extension());
			if (is_array($config)) {
				$compiler->addConfig($config);
			} elseif (is_file($config)) {
				$compiler->loadConfig($config);
			} else {
				throw new RuntimeException('Unsupported config');
			}
		}, md5(serialize([microtime(true), mt_rand(0, 1000), $config])));

		return new $className();
	}

}
