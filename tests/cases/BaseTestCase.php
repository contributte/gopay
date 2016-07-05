<?php

use Markette\Gopay\DI\Extension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\TestCase;

abstract class BaseTestCase extends TestCase
{

    /**
     * @param mixed $config
     * @return Container
     */
    protected function createContainer($config = NULL)
    {
        $loader = new ContainerLoader(TEMP_DIR);
        $className = $loader->load(function (Compiler $compiler) use ($config) {
            $compiler->addExtension('gopay', new Extension());
            if (is_array($config)) {
                $compiler->addConfig($config);
            } else if (is_file($config)) {
                $compiler->loadConfig($config);
            }
        }, md5(serialize([microtime(TRUE), $config])));

        return new $className;
    }
}
