<?php

use Markette\Gopay\Extension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\TestCase;

abstract class BaseTestCase extends TestCase
{

    /**
     * @param string $file
     * @return Container
     */
    protected function createContainer($file)
    {
        $loader = new ContainerLoader(TEMP_DIR);
        $key = 'key';
        $className = $loader->load($key, function (Compiler $compiler) use ($file) {
            $compiler->addExtension('gopay', new Extension());
            $compiler->loadConfig(__DIR__ . '/config/' . $file);
        });

        return new $className;
    }
}
