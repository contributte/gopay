<?php

/**
 * Test: Markette\Gopay\Service\PaymentService
 *
 * @testCase
 */

use Markette\Gopay\Entity\ReturnedPayment;
use Markette\Gopay\Gopay;
use Markette\Gopay\Service\PaymentService;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class PaymentServiceTest extends BasePaymentTestCase
{

    public function testPay()
    {
        $gopay = $this->createPaymentGopay();

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');
        $service->setLang(Gopay::LANG_EN);

        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        $response = $service->pay($payment, $gopay::METHOD_CARD_GPKB, $this->createNullCallback());

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::same('https://testgw.gopay.cz/gw/pay-full-v2?sessionInfo.targetGoId=1234567890&sessionInfo.paymentSessionId=3000000001&sessionInfo.encryptedSignature=999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
            $response->getUrl()
        );
        Assert::same(302, $response->getCode());
    }

    public function testPayInline()
    {
        $gopay = $this->createPaymentGopay();

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');
        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        $response = $service->payInline($payment, $gopay::METHOD_CARD_GPKB, $this->createNullCallback());

        Assert::type('array', $response);
        Assert::count(2, $response);
        Assert::same('https://testgw.gopay.cz/gw/v3/3000000001',
            $response['url']
        );

        Assert::same('999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
            $response['signature']
        );
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
        $gopay = Mockery::mock(Gopay::class);

        $service = new PaymentService($gopay);
        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        Assert::type('Markette\Gopay\Entity\Payment', $payment);
    }

    public function testRestorePayment()
    {
        $gopay = Mockery::mock(Gopay::class);

        $service = new PaymentService($gopay);
        $payment = $service->restorePayment(['sum' => 999, 'customer' => []], []);

        Assert::type('Markette\Gopay\Entity\ReturnedPayment', $payment);
    }

    public function testPayExceptions()
    {
        $gopay = Mockery::mock(Gopay::class);

        $callback = $this->createNullCallback();

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');
        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

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
    }

    public function testCallbackCalled()
    {
        $gopay = $this->createPaymentGopay();

        $called = NULL;
        $callback = function ($id) use (&$called) {
            $called = TRUE;
            Assert::same(3000000001, $id);
        };

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);
        $service->pay($payment, $gopay::METHOD_CARD_GPKB, $callback);

        Assert::true($called);
    }

    public function testPayThrowsException()
    {
        $gopay = $this->createPaymentGopay();
        $exmsg = "Fatal error during paying";
        $gopay->getSoap()->shouldReceive('createPayment')->twice()->andThrow('Exception', $exmsg);

        $service = new PaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        Assert::throws(function () use ($service, $payment) {
            $response = $service->pay($payment, Gopay::METHOD_CARD_GPKB, function () {
            });
        }, 'Markette\Gopay\Exception\GopayException', $exmsg);

        Assert::throws(function () use ($service, $payment) {
            $response = $service->payInline($payment, Gopay::METHOD_CARD_GPKB, function () {
            });
        }, 'Markette\Gopay\Exception\GopayException', $exmsg);
    }

    public function testAllowChangeChannel()
    {
        $gopay = Mockery::mock(Gopay::class);

        $service = Mockery::mock(PaymentService::class, [$gopay])->makePartial();
        $service->shouldAllowMockingProtectedMethods();
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');
        $service->addChannel($gopay::METHOD_CSAS, 'CSAS');

        $service->allowChangeChannel(FALSE);
        Assert::equal([
            $gopay::METHOD_CSAS
        ], $service->getPaymentChannels($gopay::METHOD_CSAS));

        $service->allowChangeChannel(TRUE);
        Assert::equal([
            $gopay::METHOD_CARD_GPKB,
            $gopay::METHOD_CSAS
        ], $service->getPaymentChannels($gopay::METHOD_CARD_GPKB));
    }

}

$test = new PaymentServiceTest();
$test->run();
