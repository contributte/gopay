<?php

/**
 * Test: Markette\Gopay\Gopay
 *
 * @testCase
 */

use Markette\Gopay\Gopay;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

class GopayTest extends BaseTestCase
{

    public function testGetters()
    {
        $config = Mockery::mock('Markette\Gopay\Config');
        $soap = Mockery::mock('Markette\Gopay\Api\GopaySoap');
        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $gopay = new Gopay($config, $soap, $helper);

        Assert::same($config, $gopay->getConfig());
        Assert::same($soap, $gopay->getSoap());
        Assert::same($helper, $gopay->getHelper());
    }
}

$test = new GopayTest();
$test->run();
