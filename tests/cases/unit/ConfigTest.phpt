<?php

/**
 * Test: Markette\Gopay\Config
 *
 * @testCase
 */

use Markette\Gopay\Config;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

class ConfigTest extends BaseTestCase
{

    public function testGetters()
    {
        $config = new Config(1, 2, TRUE);

        Assert::equal(1.0, $config->getGopayId());
        Assert::equal('2', $config->getGopaySecretKey());
        Assert::true($config->isTestMode());

        $config = new Config(11, 22, FALSE);

        Assert::equal(11.0, $config->getGopayId());
        Assert::equal('22', $config->getGopaySecretKey());
        Assert::false($config->isTestMode());
    }
}

$test = new ConfigTest();
$test->run();
