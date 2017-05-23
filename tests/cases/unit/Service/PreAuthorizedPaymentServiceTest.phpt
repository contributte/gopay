<?php

/**
 * Test: Markette\Gopay\Service\PreAuthorizedPaymentService
 *
 * @testCase
 */

namespace Tests\Unit\Service;

use Markette\Gopay\Api\GopayConfig;
use Markette\Gopay\Entity\PreAuthorizedPayment;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Gopay;
use Markette\Gopay\Service\PreAuthorizedPaymentService;
use Mockery;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;
use Tests\Engine\BasePaymentTestCase;

require __DIR__ . '/../../../bootstrap.php';

class PreAuthorizedPaymentServiceTest extends BasePaymentTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testRecurrentPay()
	{
		$gopay = $this->createPreAuthorizedPaymentGopay();

		$service = new PreAuthorizedPaymentService($gopay);
		$service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

		$payment = $service->createPayment(['sum' => 999, 'customer' => []]);

		$response = $service->payPreAuthorized($payment, $gopay::METHOD_CARD_GPKB, $this->createNullCallback());

		Assert::type(RedirectResponse::class, $response);
		Assert::same(
			GopayConfig::TEST_URL . 'gw/pay-full-v2?sessionInfo.targetGoId=1234567890&sessionInfo.paymentSessionId=3000000001&sessionInfo.encryptedSignature=999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
			$response->getUrl()
		);
		Assert::same(302, $response->getCode());
	}

	/**
	 * @test
	 * @return void
	 */
	public function testRecurrentPayInline()
	{
		$gopay = $this->createPreAuthorizedPaymentGopay();

		$service = new PreAuthorizedPaymentService($gopay);
		$service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

		$payment = $service->createPayment(['sum' => 999, 'customer' => []]);

		$response = $service->payPreAuthorizedInline($payment, $gopay::METHOD_CARD_GPKB, $this->createNullCallback());

		Assert::type('array', $response);
		Assert::count(2, $response);
		Assert::same(
			GopayConfig::TEST_URL . 'gw/v3/3000000001',
			$response['url']
		);

		Assert::same(
			'999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
			$response['signature']
		);
	}

	/**
	 * @test
	 * @return void
	 */
	public function testCreatePayment()
	{
		$gopay = Mockery::mock(Gopay::class);

		$service = new PreAuthorizedPaymentService($gopay);
		$payment = $service->createPayment(['sum' => 999, 'customer' => []]);

		Assert::type(PreAuthorizedPayment::class, $payment);
	}

	/**
	 * @test
	 * @return void
	 */
	public function testPayThrowsException()
	{
		$gopay = $this->createPreAuthorizedPaymentGopay();
		$exmsg = 'Fatal error during paying';
		$gopay->getSoap()->shouldReceive('createPreAutorizedPayment')->twice()->andThrow('Exception', $exmsg);

		$service = new PreAuthorizedPaymentService($gopay);
		$service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

		$payment = $service->createPayment(['sum' => 999, 'customer' => []]);

		Assert::throws(function () use ($service, $payment) {
			$response = $service->payPreAuthorized($payment, Gopay::METHOD_CARD_GPKB, function () {
			});
		}, GopayException::class, $exmsg);

		Assert::throws(function () use ($service, $payment) {
			$response = $service->payPreAuthorizedInline($payment, Gopay::METHOD_CARD_GPKB, function () {
			});
		}, GopayException::class, $exmsg);

		$gopay->getSoap()->mockery_verify();
	}

	/**
	 * @test
	 * @return void
	 */
	public function testCancelRecurrent()
	{
		$gopay = $this->createGopay();
		$service = new PreAuthorizedPaymentService($gopay);
		$paymentSessionId = 3000000001;

		$gopay->getSoap()
			->shouldReceive('voidAuthorization')
			->once()
			->with(Mockery::mustBe($paymentSessionId), Mockery::type('float'), Mockery::type('string'))
			->andReturnUsing(function () {
				Assert::truthy(TRUE);
			});
		$service->cancelPreAuthorized(3000000001);

		$gopay->getSoap()->mockery_verify();
	}

	/**
	 * @test
	 * @return void
	 */
	public function testCancelRecurrentException()
	{
		$gopay = $this->createGopay();
		$paymentSessionId = 3000000001;
		$exmsg = 'Fatal error during paying';
		$service = new PreAuthorizedPaymentService($gopay);

		$gopay->getSoap()
			->shouldReceive('voidAuthorization')
			->once()
			->with(Mockery::mustBe($paymentSessionId), Mockery::type('float'), Mockery::type('string'))
			->andThrow('Exception', $exmsg);

		Assert::throws(function () use ($service, $paymentSessionId) {
			$service->cancelPreAuthorized($paymentSessionId);
		}, GopayException::class, $exmsg);

		$gopay->getSoap()->mockery_verify();
	}

	/**
	 * @test
	 * @return void
	 */
	public function testCaptureRecurrent()
	{
		$gopay = $this->createGopay();
		$service = new PreAuthorizedPaymentService($gopay);
		$paymentSessionId = 3000000001;

		$gopay->getSoap()
			->shouldReceive('capturePayment')
			->once()
			->with(Mockery::mustBe($paymentSessionId), Mockery::type('float'), Mockery::type('string'))
			->andReturnUsing(function () {
				Assert::truthy(TRUE);
			});
		$service->capturePreAuthorized(3000000001);

		$gopay->getSoap()->mockery_verify();
	}

	/**
	 * @test
	 * @return void
	 */
	public function testCaputreRecurrentException()
	{
		$gopay = $this->createGopay();
		$paymentSessionId = 3000000001;
		$exmsg = 'Fatal error during paying';
		$service = new PreAuthorizedPaymentService($gopay);

		$gopay->getSoap()
			->shouldReceive('capturePayment')
			->once()
			->with(Mockery::mustBe($paymentSessionId), Mockery::type('float'), Mockery::type('string'))
			->andThrow('Exception', $exmsg);

		Assert::throws(function () use ($service, $paymentSessionId) {
			$service->capturePreAuthorized($paymentSessionId);
		}, GopayException::class, $exmsg);

		$gopay->getSoap()->mockery_verify();
	}

}

$test = new PreAuthorizedPaymentServiceTest();
$test->run();
