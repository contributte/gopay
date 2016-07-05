<?php

/**
 * Test: Markette\Gopay\DI\Extension
 *
 * @testCase
 */

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Markette\Gopay\Config;
use Markette\Gopay\Gopay;
use Markette\Gopay\Service\PaymentService;
use Markette\Gopay\Service\PreAuthorizedPaymentService;
use Markette\Gopay\Service\RecurrentPaymentService;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class ExtensionTest extends BaseTestCase
{

    public function testServiceCreation()
    {
        $container = $this->createContainer(__DIR__ . '/../../files/config/default.neon');

        Assert::type(GopaySoap::class, $container->getService('gopay.driver'));
        Assert::type(GopayHelper::class, $container->getService('gopay.helper'));
        Assert::type(Config::class, $container->getService('gopay.config'));
        Assert::type(Gopay::class, $container->getService('gopay.gopay'));

        Assert::type(PaymentService::class, $container->getService('gopay.service.payment'));
        Assert::type(RecurrentPaymentService::class, $container->getService('gopay.service.recurrentPayment'));
        Assert::type(PreAuthorizedPaymentService::class, $container->getService('gopay.service.preAuthorizedPayment'));
    }

    public function testChannels()
    {
        $container = $this->createContainer(__DIR__ . '/../../files/config/channels.neon');
        $paymentService = $container->getService('gopay.service.payment');

        Assert::equal([
            'eu_gp_u' => (object) [
                'code' => 'eu_gp_u',
                'name' => 'Platba kartou - Česká spořitelna',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ],
            'eu_bank' => (object) [
                'code' => 'eu_bank',
                'name' => 'Běžný bankovní převod',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ],
            'SUPERCASH' => (object) [
                'code' => 'SUPERCASH',
                'name' => 'Terminál České pošty',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ],
            'cz_kb' => (object) [
                'code' => 'cz_kb',
                'name' => 'Platba KB - Mojeplatba',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ],
            'sk_otpbank' => (object) [
                'code' => 'sk_otpbank',
                'name' => 'Platba OTP banka Slovensko, a.s.',
                'logo' => 'opt-logo.png',
                'offline' => NULL,
                'description' => NULL,
            ],
        ],
            $paymentService->getChannels()
        );
    }

    public function testChangeChannel()
    {
        $property = new ReflectionProperty(PaymentService::class, 'changeChannel');
        $property->setAccessible(TRUE);

        $container = $this->createContainer([
            'gopay' => [
                'payments' => [
                    'changeChannel' => FALSE,
                ],
            ],
        ]);

        $service = $container->getService('gopay.service.payment');
        Assert::false($property->getValue($service));

        $container = $this->createContainer([
            'gopay' => [
                'payments' => [
                    'changeChannel' => TRUE,
                ],
            ],
        ]);

        $service = $container->getService('gopay.service.payment');
        Assert::true($property->getValue($service));
    }
}

$test = new ExtensionTest();
$test->run();
