<?php

/**
 * Test: Markette\Gopay\DI\Extension
 *
 * @testCase
 */

use Tester\Assert;
use Tester\FileMock;

require __DIR__ . '/../../../bootstrap.php';

class ExtensionTest extends BaseTestCase
{

    public function testServiceCreation()
    {
        $container = $this->createContainer(__DIR__ . '/../../files/config/default.neon');

        Assert::type('Markette\Gopay\Service\RecurrentPaymentService', $container->getService('gopay.service.recurrentPayment'));
        Assert::type('Markette\Gopay\Api\GopaySoap', $container->getService('gopay.driver'));
        Assert::type('Markette\Gopay\Api\GopayHelper', $container->getService('gopay.helper'));

        $paymentService = $container->getService('gopay.service.payment');
        Assert::type('Markette\Gopay\Service\PaymentService', $paymentService);

        $recurrentPaymentService = $container->getService('gopay.service.recurrentPayment');
        Assert::type('Markette\Gopay\Service\RecurrentPaymentService', $recurrentPaymentService);
    }

    public function testChannels()
    {
        $container = $this->createContainer(__DIR__ . '/../../files/config/channels.neon');
        $paymentService = $container->getService('gopay.service.payment');

        Assert::equal([
            'eu_gp_u' => (object)[
                'code' => 'eu_gp_u',
                'name' => 'Platba kartou - Česká spořitelna',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ],
            'eu_bank' => (object)[
                'code' => 'eu_bank',
                'name' => 'Běžný bankovní převod',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ],
            'SUPERCASH' => (object)[
                'code' => 'SUPERCASH',
                'name' => 'Terminál České pošty',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ],
            'cz_kb' => (object)[
                'code' => 'cz_kb',
                'name' => 'Platba KB - Mojeplatba',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ],
            'sk_otpbank' => (object)[
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
        $property = new ReflectionProperty('Markette\Gopay\Service\PaymentService', 'changeChannel');
        $property->setAccessible(TRUE);

        $container = $this->createContainer(FileMock::create('
gopay:
    payments:
        changeChannel: off
', 'neon'));

        $service = $container->getService('gopay.service.payment');
        Assert::false($property->getValue($service));

        $container = $this->createContainer(FileMock::create('
gopay:
    payments:
        changeChannel: on
', 'neon'));

        $service = $container->getService('gopay.service.payment');
        Assert::true($property->getValue($service));
    }
}

$test = new ExtensionTest();
$test->run();
