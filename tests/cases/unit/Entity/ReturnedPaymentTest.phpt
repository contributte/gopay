<?php

/**
 * Test: Markette\Gopay\Entity\ReturnPayment
 *
 * @testCase
 */

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Config;
use Markette\Gopay\Entity\ReturnedPayment;
use Markette\Gopay\Gopay;
use Tester\Assert;
use Markette\Gopay\Exception\GopayException;

require __DIR__ . '/../../../bootstrap.php';

class ReturnedPaymentTest extends BaseTestCase
{

    public function testNoGopay()
    {
        $toBeVerified = [
            'targetGoId' => 1,
            'paymentSessionId' => 2,
            'encryptedSignature' => 3,
            'orderNumber' => 4
        ];
        $returnedPayment = new ReturnedPayment([], $toBeVerified);

        Assert::throws(function () use ($returnedPayment) {
            $returnedPayment->isFraud();
        }, GopayException::class, 'No gopay set');
    }

    public function testFraud()
    {
        $config = Mockery::namedMock('Config1', 'Markette\Gopay\Config');
        $config->shouldReceive('getGopayId')->once()->andReturn(1);
        $config->shouldReceive('getGopaySecretKey')->once()->andReturn(1);

        $soap = Mockery::namedMock('GopaySoap1', 'Markette\Gopay\Api\GopaySoap');

        $helper = Mockery::namedMock('GopayHelper1', 'Markette\Gopay\Api\GopayHelper');
        $helper->shouldReceive('checkPaymentIdentity')->once()->andThrow('Exception');

        $gopay = new Gopay($config, $soap, $helper);

        $toBeVerified = [
            'targetGoId' => 1,
            'paymentSessionId' => 2,
            'encryptedSignature' => 3,
            'orderNumber' => 4
        ];
        $returnedPayment = new ReturnedPayment([], $toBeVerified);
        $returnedPayment->setGopay($gopay);

        Assert::true($returnedPayment->isFraud());

        $config->mockery_verify();
        $helper->mockery_verify();
    }

    public function testFraudFalse()
    {
        $config = Mockery::namedMock('Config2', 'Markette\Gopay\Config');
        $config->shouldReceive('getGopayId')->once()->andReturn(1);
        $config->shouldReceive('getGopaySecretKey')->once()->andReturn(1);

        $soap = Mockery::namedMock('GopaySoap2', 'Markette\Gopay\Api\GopaySoap');

        $helper = Mockery::namedMock('GopayHelper2', 'Markette\Gopay\Api\GopayHelper');
        $helper->shouldReceive('checkPaymentIdentity')->once()->andReturnNull();

        $gopay = new Gopay($config, $soap, $helper);

        $toBeVerified = [
            'targetGoId' => 1,
            'paymentSessionId' => 2,
            'encryptedSignature' => 3,
            'orderNumber' => 4
        ];
        $returnedPayment = new ReturnedPayment([], $toBeVerified);
        $returnedPayment->setGopay($gopay);

        Assert::false($returnedPayment->isFraud());

        $config->mockery_verify();
        $helper->mockery_verify();
    }

    public function testGetStatus()
    {
        $result = ['sessionState' => GopayHelper::CANCELED];
        $config = Mockery::namedMock('Config3', 'Markette\Gopay\Config');
        $config->shouldReceive('getGopayId')->once()->andReturn(1);
        $config->shouldReceive('getGopaySecretKey')->once()->andReturn(1);

        $soap = Mockery::namedMock('GopaySoap3', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('isPaymentDone')->once()->andReturn($result);

        $helper = Mockery::namedMock('GopayHelper3', 'Markette\Gopay\Api\GopayHelper');

        $gopay = new Gopay($config, $soap, $helper);

        $returnedPayment = new ReturnedPayment([], ['paymentSessionId' => 1]);
        $returnedPayment->setGopay($gopay);

        $status = $returnedPayment->getStatus();
        Assert::same($result, $status);
        Assert::equal($status, $status);

        $soap->mockery_verify();
    }

    public function testStatuses()
    {
        $result = ['sessionState' => GopayHelper::CANCELED];
        $config = Mockery::namedMock('Config4', 'Markette\Gopay\Config');
        $config->shouldReceive('getGopayId')->once()->andReturn(1);
        $config->shouldReceive('getGopaySecretKey')->once()->andReturn(1);

        $soap = Mockery::namedMock('GopaySoap4', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('isPaymentDone')->once()->andReturn($result);

        $helper = Mockery::namedMock('GopayHelper4', 'Markette\Gopay\Api\GopayHelper');

        $gopay = new Gopay($config, $soap, $helper);

        $returnedPayment = new ReturnedPayment([], ['paymentSessionId' => 1]);
        $returnedPayment->setGopay($gopay);

        Assert::false($returnedPayment->isAuthorized());
        Assert::false($returnedPayment->isPaid());
        Assert::false($returnedPayment->isRefunded());
        Assert::false($returnedPayment->isTimeouted());
        Assert::false($returnedPayment->isWaiting());

        Assert::true($returnedPayment->isCanceled());

        $soap->mockery_verify();
    }
}

$test = new ReturnedPaymentTest();
$test->run();
