<?php

use Markette\Gopay\DI\Extension;
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
        $className = $loader->load($config, function (Compiler $compiler) use ($config) {
            $compiler->addExtension('gopay', new Extension());
            if (is_array($config)) {
                $compiler->addConfig($config);
            } else {
                $compiler->loadConfig($config);
            }
        });

        return new $className;
    }
}
