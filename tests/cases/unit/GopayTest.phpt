<?php

/**
 * Test: Markette\Gopay\Gopay
 *
 * @testCase
 */

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Markette\Gopay\Config;
use Markette\Gopay\Gopay;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

class GopayTest extends BaseTestCase
{

    public function testGetters()
    {
        $config = Mockery::mock(Config::class);
        $soap = Mockery::mock(GopaySoap::class);
        $helper = Mockery::mock(GopayHelper::class);

        $gopay = new Gopay($config, $soap, $helper);

        Assert::same($config, $gopay->getConfig());
        Assert::same($soap, $gopay->getSoap());
        Assert::same($helper, $gopay->getHelper());
    }
}

$test = new GopayTest();
$test->run();
