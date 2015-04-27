<?php

/**
 * Test: Extension
 *
 * @testCase
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class ExtensionTest extends BaseTestCase
{

    public function testServiceCreation()
    {
        $container = $this->createContainer('config.neon');
        $service = $container->getService('gopay.service');

        Assert::type('Markette\Gopay\Service', $service);
        Assert::type('Markette\Gopay\Api\GopaySoap', $container->getService('gopay.driver'));

        Assert::equal(array(
            'eu_gp_u' => (object)array(
                'code' => 'eu_gp_u',
                'name' => 'Platba kartou - Česká spořitelna',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ),
            'eu_bank' => (object)array(
                'code' => 'eu_bank',
                'name' => 'Běžný bankovní převod',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ),
            'SUPERCASH' => (object)array(
                'code' => 'SUPERCASH',
                'name' => 'Terminál České pošty',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ),
            'cz_kb' => (object)array(
                'code' => 'cz_kb',
                'name' => 'Platba KB - Mojeplatba',
                'logo' => NULL,
                'offline' => NULL,
                'description' => NULL,
            ),
            'sk_otpbank' => (object)array(
                'code' => 'sk_otpbank',
                'name' => 'Platba OTP banka Slovensko, a.s.',
                'logo' => 'opt-logo.png',
                'offline' => NULL,
                'description' => NULL,
            ),
        ),
            $service->getChannels()
        );
    }
}

$test = new ExtensionTest();
$test->run();
