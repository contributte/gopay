<?php

/**
 * Test: Markette\Gopay\DI\Helpers
 *
 * @testCase
 */

use Markette\Gopay\DI\Helpers;
use Markette\Gopay\Service;
use Nette\Application\UI\Form;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class HelpersTest extends BaseTestCase
{

    public function testBindForm()
    {
        $form = new Form();
        $container = $this->createContainer(__DIR__ . '/../../files/config/default.neon');

        $service = $container->getService('gopay.service.payment');
        $binder = $container->getService('gopay.form.binder');

        $callback = function () {
        };
        $service->addChannel('test', 'tetst-name', 'test-logo');
        $binder->bindPaymentButtons($service, $form, $callback);


        Assert::type('Markette\Gopay\Form\PaymentButton', $form->getComponent('gopayChanneleu_gp_u'));
        Assert::type('Markette\Gopay\Form\PaymentButton', $form->getComponent('gopayChanneleu_bank'));
        Assert::type('Markette\Gopay\Form\PaymentButton', $form->getComponent('gopayChannelSUPERCASH'));
        Assert::type('Markette\Gopay\Form\ImagePaymentButton', $form->getComponent('gopayChanneltest'));

        Assert::same([$callback], $form->getComponent('gopayChanneleu_gp_u')->onClick);
        Assert::same([$callback], $form->getComponent('gopayChanneleu_bank')->onClick);
        Assert::same([$callback], $form->getComponent('gopayChannelSUPERCASH')->onClick);
    }

    public function testRegisterPaymentButtonsDI()
    {
        $container = $this->createContainer(__DIR__ . '/../../files/config/default.neon');
        Helpers::registerAddPaymentButtonsUsingDependencyContainer($container);

        $form = new Form();
        $callback = function () {
        };
        $form->addPaymentButtons($callback);
        Assert::same([$callback], $form->getComponent('gopayChanneleu_gp_u')->onClick);

        $form = new Form();
        $form->addPaymentButton('eu_gp_u');
        Assert::null($form->getComponent('gopayChanneleu_gp_u')->onClick);
    }

    public function testRegisterPaymentButtons()
    {
        $container = $this->createContainer(__DIR__ . '/../../files/config/default.neon');
        Helpers::registerAddPaymentButtons($container->getService('gopay.form.binder'), $container->getService('gopay.service.payment'));

        $form = new Form();
        $callback = function () {
        };
        $form->addPaymentButtons($callback);
        Assert::same([$callback], $form->getComponent('gopayChanneleu_gp_u')->onClick);

        $form = new Form();
        $form->addPaymentButton('eu_gp_u');
        Assert::null($form->getComponent('gopayChanneleu_gp_u')->onClick);
    }

    public function testNotAllowedChannel()
    {
        $container = $this->createContainer(__DIR__ . '/../../files/config/default.neon');
        Helpers::registerAddPaymentButtons($container->getService('gopay.form.binder'), $container->getService('gopay.service.payment'));

        $form = new Form();
        Assert::throws(function () use ($form) {
            $form->addPaymentButton('test');
        }, 'Markette\Gopay\Exception\InvalidArgumentException', "Channel 'test' is not allowed.");
    }
}

$test = new HelpersTest();
$test->run();
