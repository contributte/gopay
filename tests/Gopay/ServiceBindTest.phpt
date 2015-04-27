<?php

/**
 * Test: Service - buttons binding
 *
 * @testCase
 */

use Markette\Gopay\Service;
use Nette\Application\UI\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class ServiceBindTest extends BaseTestCase
{

    public function testBindForm()
    {
        $form = new Form();
        $gopay = $this->createContainer('config.neon')->getService('gopay.service');
        $callback = function () {
        };
        $gopay->addChannel('test', 'tetst-name', 'test-logo');
        $gopay->bindPaymentButtons($form, $callback);


        Assert::type('Markette\Gopay\PaymentButton', $form->getComponent('gopayChanneleu_gp_u'));
        Assert::type('Markette\Gopay\PaymentButton', $form->getComponent('gopayChanneleu_bank'));
        Assert::type('Markette\Gopay\PaymentButton', $form->getComponent('gopayChannelSUPERCASH'));
        Assert::type('Markette\Gopay\ImagePaymentButton', $form->getComponent('gopayChanneltest'));

        Assert::same(array($callback), $form->getComponent('gopayChanneleu_gp_u')->onClick);
        Assert::same(array($callback), $form->getComponent('gopayChanneleu_bank')->onClick);
        Assert::same(array($callback), $form->getComponent('gopayChannelSUPERCASH')->onClick);
    }

    public function testRegisterPaymentButtonsDI()
    {
        $container = $this->createContainer('config.neon');
        Service::registerAddPaymentButtonsUsingDependencyContainer($container, 'gopay.service');

        $form = new Form();
        $callback = function () {
        };
        $form->addPaymentButtons($callback);
        Assert::same(array($callback), $form->getComponent('gopayChanneleu_gp_u')->onClick);

        $form = new Form();
        $form->addPaymentButton('eu_gp_u');
        Assert::null($form->getComponent('gopayChanneleu_gp_u')->onClick);
    }

    public function testRegisterPaymentButtons()
    {
        $container = $this->createContainer('config.neon');
        Service::registerAddPaymentButtons($container->getService('gopay.service'));

        $form = new Form();
        $callback = function () {
        };
        $form->addPaymentButtons($callback);
        Assert::same(array($callback), $form->getComponent('gopayChanneleu_gp_u')->onClick);

        $form = new Form();
        $form->addPaymentButton('eu_gp_u');
        Assert::null($form->getComponent('gopayChanneleu_gp_u')->onClick);
    }

    public function testNotAllowedChannel()
    {
        $container = $this->createContainer('config.neon');
        $service = $container->getService('gopay.service');

        $form = new Form();
        Assert::throws(function () use ($service, $form) {
            $service->bindPaymentButton('test', $form);
        }, '\InvalidArgumentException', "Channel 'test' is not allowed.");
    }
}

$test = new ServiceBindTest();
$test->run();
