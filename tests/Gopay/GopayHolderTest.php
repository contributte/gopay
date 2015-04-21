<?php

/**
 * Test: GopayHolder
 *
 * @testCase
 */

use Tester\Assert;
use Markette\Gopay\GopayHolder;

require __DIR__ . '/../bootstrap.php';

class GopayHolderTest extends BaseTestCase
{

    public function testGopayHolder()
    {
        $holder = GopayHolder::getInstance();

        Assert::type($holder->getHelper(), 'Markette\Gopay\Api\GopayHelper');
        Assert::type($holder->getSoap(), 'Markette\Gopay\Api\GopaySoap');
    }

    public function testHelper()
    {
        $return = FALSE;
        $mock = Mockery::mock('Markette\Gopay\Api\GopayHelper');
        $mock->shouldReceive('test')->once()->andReturn($return);

        $holder = GopayHolder::getInstance();
        $holder->setHelper($mock);

        Assert::same($holder->getHelper()->test(), $return);
    }
    public function testSoap()
    {
        $return = FALSE;
        $mock = Mockery::mock('Markette\Gopay\Api\GopaySoap');
        $mock->shouldReceive('test')->once()->andReturn($return);

        $holder = GopayHolder::getInstance();
        $holder->setSoap($mock);

        Assert::same($holder->getSoap()->test(), $return);
    }
}

$test = new GopayHolderTest();
$test->run();
