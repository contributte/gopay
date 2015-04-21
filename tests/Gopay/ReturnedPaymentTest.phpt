<?php

/**
 * Test: ReturnPayment
 *
 * @testCase
 */

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\GopayHolder;
use Markette\Gopay\ReturnedPayment;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class ReturnedPaymentTest extends BaseTestCase
{

    public function testReturnedPayment()
    {
        $values = array('sum' => 999, 'customer' => array(), 'variable' => 7);
        $toBeVerified = array(
            'targetGoId' => 1234567890,
            'paymentSessionId' => 3000000001,
            'encryptedSignature' => '0bd97288ec61cb4485510f820a8a4772108e5799bd57b2df2173e088e0c7d419a0efb866855b27b7',
            'orderNumber' => 7
        );
        $returnedPayment = new ReturnedPayment($values, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', $toBeVerified);
        Assert::false($returnedPayment->isFraud());
    }

    public function testFraud()
    {
        $result = array('sessionState' => GopayHelper::CANCELED);
        $mock = Mockery::mock('Markette\Gopay\Api\GopayHelper');
        $mock->shouldReceive('checkPaymentIdentity')->once()->andThrow('Exception');
        GopayHolder::getInstance()->setHelper($mock);

        $toBeVerified = array(
            'targetGoId' => 1234567890,
            'paymentSessionId' => 3000000001,
            'encryptedSignature' => '0bd97288ec61cb4485510f820a8a4772108e5799bd57b2df2173e088e0c7d419a0efb866855b27b7',
            'orderNumber' => 7
        );
        $returnedPayment = new ReturnedPayment(array(), NULL, NULL, $toBeVerified);

        Assert::true($returnedPayment->isFraud());
    }
    public function testGetStatus()
    {
        $result = array('sessionState' => GopayHelper::CANCELED);
        $mock = Mockery::mock('Markette\Gopay\Api\GopaySoap');
        $mock->shouldReceive('isPaymentDone')->once()->andReturn($result);
        GopayHolder::getInstance()->setSoap($mock);

        $returnedPayment = new ReturnedPayment(array(), NULL, NULL, array('paymentSessionId' => 1));

        $status = $returnedPayment->getStatus();
        Assert::same($result, $status);
        Assert::equal($status, $status);
    }

    public function testStatuses()
    {
        $result = array('sessionState' => GopayHelper::CANCELED);
        $mock = Mockery::mock('Markette\Gopay\Api\GopaySoap');
        $mock->shouldReceive('isPaymentDone')->once()->andReturn($result);
        GopayHolder::getInstance()->setSoap($mock);

        $returnedPayment = new ReturnedPayment(array(), NULL, NULL, array('paymentSessionId' => 1));

        Assert::false($returnedPayment->isAuthorized());
        Assert::false($returnedPayment->isPaid());
        Assert::false($returnedPayment->isRefunded());
        Assert::false($returnedPayment->isTimeouted());
        Assert::false($returnedPayment->isWaiting());

        Assert::true($returnedPayment->isCanceled());
    }
}


$test = new ReturnedPaymentTest();
$test->run();
