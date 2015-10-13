<?php

/**
 * Test: Markette\Gopay\Service\PaymentService
 *
 * @testCase
 */

use Markette\Gopay\Config;
use Markette\Gopay\Entity\Payment;
use Markette\Gopay\Entity\ReturnedPayment;
use Markette\Gopay\Gopay;
use Markette\Gopay\Service\PaymentService;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class PaymentServiceTest extends BaseTestCase
{

    public function testPay()
    {
        $soap = Mockery::namedMock('GopaySoap1', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->once()->andReturn(3000000001);

        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $payment = new Payment(['sum' => 999, 'customer' => []]);
        $callback = function ($id) {
        };

        $gopay = new Gopay(
            new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE),
            $soap,
            $helper
        );

        $lang = $gopay::LANG_CS;

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');
        $service->setLang($lang);

        $response = $service->pay($payment, $gopay::METHOD_CARD_GPKB, $callback);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::same('https://testgw.gopay.cz/gw/pay-full-v2?sessionInfo.targetGoId=1234567890&sessionInfo.paymentSessionId=3000000001&sessionInfo.encryptedSignature=999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
            $response->getUrl()
        );
        Assert::same(302, $response->getCode());
        $soap->mockery_verify();
    }

    public function testPayInline()
    {
        $soap = Mockery::namedMock('GopaySoap2', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->once()->andReturn(3000000001);

        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $payment = new Payment(['sum' => 999, 'customer' => []]);
        $callback = function ($id) {
        };

        $gopay = new Gopay(
            new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE),
            $soap,
            $helper
        );

        $lang = $gopay::LANG_CS;

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');
        $service->setLang($lang);

        $response = $service->payInline($payment, $gopay::METHOD_CARD_GPKB, $callback);

        Assert::type('array', $response);
        Assert::count(2, $response);
        Assert::same('https://testgw.gopay.cz/gw/v3/3000000001',
            $response['url']
        );

        Assert::same('999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
            $response['signature']
        );

        $soap->mockery_verify();
    }

    public function testUrls()
    {
        $service = $this->createContainer(__DIR__ . '/../../files/config/default.neon')->getService('gopay.service.payment');

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
        $service = $this->createContainer(__DIR__ . '/../../files/config/default.neon')->getService('gopay.service.payment');

        Assert::exception(function () use ($service) {
            $service->setLang('de');
        }, 'Markette\Gopay\Exception\InvalidArgumentException');
    }

    public function testDuplicateChannel()
    {
        $service = $this->createContainer(__DIR__ . '/../../files/config/default.neon')->getService('gopay.service.payment');
        $service->addChannel('test', 'test-name');

        Assert::exception(function () use ($service) {
            $service->addChannel('test', 'test-name');
        }, 'Markette\Gopay\Exception\InvalidArgumentException');

    }

    public function testCreatePayment()
    {
        $service = $this->createContainer(__DIR__ . '/../../files/config/default.neon')->getService('gopay.service.payment');
        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        Assert::type('Markette\Gopay\Entity\Payment', $payment);
    }

    public function testRestorePayment()
    {
        $service = $this->createContainer(__DIR__ . '/../../files/config/default.neon')->getService('gopay.service.payment');
        $payment = $service->restorePayment(['sum' => 999, 'customer' => []], []);

        Assert::type('Markette\Gopay\Entity\ReturnedPayment', $payment);
    }

    public function testPayExceptions()
    {
        $soap = Mockery::namedMock('GopaySoap3', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->never();

        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $payment = new Payment(['sum' => 999, 'customer' => []]);
        $callback = function ($id) {
        };

        $gopay = new Gopay(
            new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE),
            $soap,
            $helper
        );

        $payment = new Payment(['sum' => 999, 'customer' => []]);

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

        Assert::exception(function () use ($payment, $callback, $service) {
            $service->pay($payment, 'nonexisting', $callback);
        }, 'Markette\Gopay\Exception\InvalidArgumentException', "Payment channel 'nonexisting' is not supported");

        $paymentRet = new ReturnedPayment(['sum' => 999, 'customer' => []], []);
        Assert::exception(function () use ($paymentRet, $callback, $service) {
            $service->pay($paymentRet, Gopay::METHOD_CARD_GPKB, $callback);
        }, 'Markette\Gopay\Exception\InvalidArgumentException', "Cannot use instance of 'ReturnedPayment'! This payment has been already used for paying");

        Assert::exception(function () use ($payment, $callback, $service) {
            $service->payInline($payment, 'nonexisting', $callback);
        }, 'Markette\Gopay\Exception\InvalidArgumentException', "Payment channel 'nonexisting' is not supported");

        Assert::exception(function () use ($paymentRet, $callback, $service) {
            $service->payInline($paymentRet, Gopay::METHOD_CARD_GPKB, $callback);
        }, 'Markette\Gopay\Exception\InvalidArgumentException', "Cannot use instance of 'ReturnedPayment'! This payment has been already used for paying");

        $soap->mockery_verify();
    }

    public function testCallbackCalled()
    {
        $soap = Mockery::namedMock('GopaySoap4', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->once()->andReturn(3000000001);

        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $payment = new Payment(['sum' => 999, 'customer' => []]);
        $callback = function ($id) {
        };

        $gopay = new Gopay(
            new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE),
            $soap,
            $helper
        );

        $payment = new Payment(['sum' => 999, 'customer' => []]);

        $called = NULL;
        $callback = function ($id) use (&$called) {
            $called = TRUE;
            Assert::same(3000000001, $id);
        };

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');
        $service->pay($payment, $gopay::METHOD_CARD_GPKB, $callback);

        Assert::true($called);
        $soap->mockery_verify();
    }

    public function testPayThrowsException()
    {
        $exmsg = "Fatal error during paying";
        $soap = Mockery::namedMock('GopaySoap5', 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->twice()->andThrow('Exception', $exmsg);

        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $payment = new Payment(['sum' => 999, 'customer' => []]);
        $callback = function ($id) {
        };

        $gopay = new Gopay(
            new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE),
            $soap,
            $helper
        );

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

        Assert::throws(function () use ($service, $payment) {
            $response = $service->pay($payment, Gopay::METHOD_CARD_GPKB, function () {
            });
        }, 'Markette\Gopay\Exception\GopayException', $exmsg);

        Assert::throws(function () use ($service, $payment) {
            $response = $service->payInline($payment, Gopay::METHOD_CARD_GPKB, function () {
            });
        }, 'Markette\Gopay\Exception\GopayException', $exmsg);

        $soap->mockery_verify();
    }

}

$test = new PaymentServiceTest();
$test->run();
