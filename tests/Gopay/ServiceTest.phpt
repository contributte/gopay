<?php

/**
 * Test: Service
 *
 * @testCase
 */

use Markette\Gopay\Payment;
use Markette\Gopay\ReturnedPayment;
use Markette\Gopay\Service;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class ServiceTest extends BaseTestCase
{

    public function testPay()
    {
        $soap = Mockery::mock('Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->once()->andReturn(3000000001);

        $payment = new Payment(array('sum' => 999, 'customer' => array()));
        $callback = function ($id) {
        };

        $lang = Service::LANG_CS;

        $service = new Service($soap, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);
        $service->addChannel(Service::METHOD_CARD_GPKB, 'KB');
        $service->setLang($lang);

        $response = $service->pay($payment, Service::METHOD_CARD_GPKB, $callback);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::same('https://testgw.gopay.cz/gw/pay-full-v2?sessionInfo.targetGoId=1234567890&sessionInfo.paymentSessionId=3000000001&sessionInfo.encryptedSignature=999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
            $response->getUrl()
        );
        Assert::same(302, $response->getCode());
    }

    public function testUrls()
    {
        $service = $this->createContainer('config.neon')->getService('gopay.service');

        $service->setSuccessUrl('www.nette.org');
        Assert::same('http://www.nette.org', $service->getSuccessUrl());
        $service->setSuccessUrl('http://www.travis-ci.org');
        Assert::same('http://www.travis-ci.org', $service->getSuccessUrl());
        $service->setSuccessUrl('https://www.github.com');
        Assert::same('https://www.github.com', $service->getSuccessUrl());

        $service->setFailureUrl('www.nette.org');
        Assert::same('http://www.nette.org', $service->getFailureUrl());
        $service->setFailureUrl('http://www.travis-ci.org');
        Assert::same('http://www.travis-ci.org', $service->getFailureUrl());
        $service->setFailureUrl('https://www.github.com');
        Assert::same('https://www.github.com', $service->getFailureUrl());
    }

    public function testUnknownLangFails()
    {
        $service = $this->createContainer('config.neon')->getService('gopay.service');

        Assert::exception(function () use ($service) {
            $service->setLang('de');
        }, '\InvalidArgumentException');
    }

    public function testDuplicateChannel()
    {
        $service = $this->createContainer('config.neon')->getService('gopay.service');
        $service->addChannel('test', 'test-name');

        Assert::exception(function () use ($service) {
            $service->addChannel('test', 'test-name');
        }, '\InvalidArgumentException');

    }

    public function testCreatePayment()
    {
        $service = $this->createContainer('config.neon')->getService('gopay.service');
        $payment = $service->createPayment(array('sum' => 999, 'customer' => array()));

        Assert::type('Markette\Gopay\Payment', $payment);
    }

    public function testRestorePayment()
    {
        $service = $this->createContainer('config.neon')->getService('gopay.service');
        $payment = $service->restorePayment(array('sum' => 999, 'customer' => array()), array());

        Assert::type('Markette\Gopay\ReturnedPayment', $payment);
    }


    public function testPayExceptions()
    {
        $soap = Mockery::mock('Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->once()->andReturn(3000000001);

        $payment = new Payment(array('sum' => 999, 'customer' => array()));
        $callback = function ($id) {
        };

        $service = new Service($soap, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);
        $service->addChannel(Service::METHOD_CARD_GPKB, 'KB');

        Assert::exception(function () use ($payment, $callback, $service) {
            $service->pay($payment, 'nonexisting', $callback);
        }, '\InvalidArgumentException', "Payment channel 'nonexisting' is not supported");

        $payment = new ReturnedPayment(array('sum' => 999, 'customer' => array()), 1234567890, 'fruC9a9e8ajuwrace4r3chaxu');
        Assert::exception(function () use ($payment, $callback, $service) {
            $service->pay($payment, Service::METHOD_CARD_GPKB, $callback);
        }, '\InvalidArgumentException', "Cannot use instance of 'ReturnedPayment'! This payment has been already used for paying");
    }

    public function testCallbackCalled()
    {
        $soap = Mockery::mock('Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->once()->andReturn(3000000001);

        $payment = new Payment(array('sum' => 999, 'customer' => array()));
        $callback = function ($id) use (&$called) {
            $called = TRUE;
            Assert::same(3000000001, $id);
        };

        $service = new Service($soap, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);
        $service->addChannel(Service::METHOD_CARD_GPKB, 'KB');
        $service->pay($payment, Service::METHOD_CARD_GPKB, $callback);

        Assert::true($called);
    }

    public function testPayThrowsException()
    {
        $exmsg = "Fatal error during paying";
        $soap = Mockery::mock('Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->once()->andThrow('Exception', $exmsg);
        $payment = new Payment(array('sum' => 999, 'customer' => array()));

        $service = new Service($soap, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);
        $service->addChannel(Service::METHOD_CARD_GPKB, 'KB');

        Assert::throws(function() use ($service, $payment) {
            $response = $service->pay($payment, Service::METHOD_CARD_GPKB, function(){});
        }, 'Markette\Gopay\GopayException', $exmsg);
    }

}

$test = new ServiceTest();
$test->run();
