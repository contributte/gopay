<?php

use Markette\Gopay\Extension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\TestCase;

abstract class BaseTestCase extends TestCase
{

    /**
     * @param string $config
     * @return Container
     */
    protected function createContainer($config)
    {
        $loader = new ContainerLoader(TEMP_DIR);
        $key = 'key';
        $className = $loader->load($key, function (Compiler $compiler) use ($config) {
            $compiler->addExtension('gopay', new Extension());
            $compiler->loadConfig(__DIR__ . '/config/' . $config);
        });

        return new $className;
    }
}
