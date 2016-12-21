<?php

/**
 * Test: Markette\Gopay\DI\Helpers
 *
 * @testCase
 */

namespace Tests\Unit\DI;

use Markette\Gopay\DI\Helpers;
use Markette\Gopay\Exception\InvalidArgumentException;
use Markette\Gopay\Form\ImagePaymentButton;
use Markette\Gopay\Form\PaymentButton;
use Nette\Application\UI\Form;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class HelpersTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testBindForm()
	{
		$form = new Form();
		$container = $this->createContainer(FIXTURES_DIR . '/config/default.neon');

		$service = $container->getService('gopay.service.payment');
		$binder = $container->getService('gopay.form.binder');

		$callback = function () {
		};
		$service->addChannel('test', 'tetst-name', 'test-logo');
		$binder->bindPaymentButtons($service, $form, $callback);

		Assert::type(PaymentButton::class, $form->getComponent('gopayChanneleu_gp_u'));
		Assert::type(PaymentButton::class, $form->getComponent('gopayChanneleu_bank'));
		Assert::type(PaymentButton::class, $form->getComponent('gopayChannelSUPERCASH'));
		Assert::type(ImagePaymentButton::class, $form->getComponent('gopayChanneltest'));

		Assert::same([$callback], $form->getComponent('gopayChanneleu_gp_u')->onClick);
		Assert::same([$callback], $form->getComponent('gopayChanneleu_bank')->onClick);
		Assert::same([$callback], $form->getComponent('gopayChannelSUPERCASH')->onClick);
	}

	/**
	 * @test
	 * @return void
	 */
	public function testRegisterPaymentButtonsDi()
	{
		$container = $this->createContainer(FIXTURES_DIR . '/config/default.neon');
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

	/**
	 * @test
	 * @return void
	 */
	public function testRegisterPaymentButtons()
	{
		$container = $this->createContainer(FIXTURES_DIR . '/config/default.neon');
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

	/**
	 * @test
	 * @return void
	 */
	public function testNotAllowedChannel()
	{
		$container = $this->createContainer(FIXTURES_DIR . '/config/default.neon');
		Helpers::registerAddPaymentButtons($container->getService('gopay.form.binder'), $container->getService('gopay.service.payment'));

		$form = new Form();
		Assert::throws(function () use ($form) {
			$form->addPaymentButton('test');
		}, InvalidArgumentException::class, "Channel 'test' is not allowed.");
	}

}

$test = new HelpersTest();
$test->run();
