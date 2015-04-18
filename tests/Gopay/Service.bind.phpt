<?php

/**
 * Test: Service - buttons binding
 *
 * @testCase
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class ServiceBindTest extends BaseTestCase
{

    public function testBindForm()
    {
        $form = new \Nette\Application\UI\Form();
        $gopay = $this->createContainer('config.neon')->getService('gopay.service');
        $callback = function () {
        };
        $gopay->bindPaymentButtons($form, $callback);

        Assert::type('Markette\Gopay\PaymentButton', $form->getComponent('gopayChanneleu_gp_u'));
        Assert::type('Markette\Gopay\PaymentButton', $form->getComponent('gopayChanneleu_bank'));
        Assert::type('Markette\Gopay\PaymentButton', $form->getComponent('gopayChannelSUPERCASH'));

        Assert::same(array($callback), $form->getComponent('gopayChanneleu_gp_u')->onClick);
        Assert::same(array($callback), $form->getComponent('gopayChanneleu_bank')->onClick);
        Assert::same(array($callback), $form->getComponent('gopayChannelSUPERCASH')->onClick);
    }
}

$test = new ServiceBindTest();
$test->run();
