<?php

/**
 * Test: Markette\Gopay\Entity\ReturnPayment
 *
 * @testCase
 */

use Markette\Gopay\Entity\ReturnedPayment;
use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Gopay;
use Markette\Gopay\Config;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class ReturnedPaymentTest extends BaseTestCase
{

    public function testFraud()
    {
        $soap = Mockery::namedMock('GopaySoap1', 'Markette\Gopay\Api\GopaySoap');

        $helper = Mockery::namedMock('GopayHelper1', 'Markette\Gopay\Api\GopayHelper');
        $helper->shouldReceive('checkPaymentIdentity')->once()->andThrow('Exception');

        $gopay = new Gopay(
            new Config(NULL, NULL, TRUE),
            $soap,
            $helper
        );

        $toBeVerified = [
            'targetGoId' => 1234567890,
            'paymentSessionId' => 3000000001,
            'encryptedSignature' => '0bd97288ec61cb4485510f820a8a4772108e5799bd57b2df2173e088e0c7d419a0efb866855b27b7',
            'orderNumber' => 7
        ];
        $returnedPayment = new ReturnedPayment([], $toBeVerified);
        $returnedPayment->setGopay($gopay);

        Assert::true($returnedPayment->isFraud());

        $helper->mockery_verify();
    }

    public function testGetStatus()
    {
        $result = ['sessionState' => GopayHelper::CANCELED];
        $soap = Mockery::namedMock('GopaySoap2', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('isPaymentDone')->once()->andReturn($result);

        $helper = Mockery::namedMock('GopayHelper2', 'Markette\Gopay\Api\GopayHelper');

        $gopay = new Gopay(
            new Config(NULL, NULL, TRUE),
            $soap,
            $helper
        );

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
        $soap = Mockery::namedMock('GopaySoap3', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('isPaymentDone')->once()->andReturn($result);

        $helper = Mockery::namedMock('GopayHelper3', 'Markette\Gopay\Api\GopayHelper');

        $gopay = new Gopay(
            new Config(NULL, NULL, TRUE),
            $soap,
            $helper
        );

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
