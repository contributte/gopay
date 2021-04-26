<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\Service\RecurrentPaymentService
 *
 * @testCase
 */

namespace Tests\Unit\Service;

use Markette\Gopay\Api\GopayConfig;
use Markette\Gopay\Entity\RecurrentPayment;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Gopay;
use Markette\Gopay\Service\RecurrentPaymentService;
use Mockery;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;
use Tests\Engine\BasePaymentTestCase;

require __DIR__ . '/../../../bootstrap.php';

class RecurrentPaymentServiceTest extends BasePaymentTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testRecurrentPay()
	{
		$gopay = $this->createRecurrentPaymentGopay();

		$service = new RecurrentPaymentService($gopay);
		$service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

		$payment = $service->createPayment([
			'sum' => 999,
			'customer' => [],
			'recurrenceCycle' => RecurrentPayment::PERIOD_DAY,
			'recurrenceDateTo' => '3000-01-01',
			'recurrencePeriod' => 10,
		]);

		$response = $service->payRecurrent($payment, $gopay::METHOD_CARD_GPKB, $this->createNullCallback());

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
		$gopay = $this->createRecurrentPaymentGopay();

		$service = new RecurrentPaymentService($gopay);
		$service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

		$payment = $service->createPayment([
			'sum' => 999,
			'customer' => [],
			'recurrenceCycle' => RecurrentPayment::PERIOD_DAY,
			'recurrenceDateTo' => '3000-01-01',
			'recurrencePeriod' => 10,
		]);

		$response = $service->payRecurrentInline($payment, $gopay::METHOD_CARD_GPKB, $this->createNullCallback());

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

		$service = new RecurrentPaymentService($gopay);
		$payment = $service->createPayment(['sum' => 999, 'customer' => []]);

		Assert::type(RecurrentPayment::class, $payment);
	}

	/**
	 * @test
	 * @return void
	 */
	public function testPayThrowsException()
	{
		$gopay = $this->createRecurrentPaymentGopay();
		$exmsg = 'Fatal error during paying';
		$gopay->getSoap()->shouldReceive('createRecurrentPayment')->twice()->andThrow('Exception', $exmsg);

		$service = new RecurrentPaymentService($gopay);
		$service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

		$payment = $service->createPayment(['sum' => 999, 'customer' => []]);

		Assert::throws(function () use ($service, $payment) {
			$service->payRecurrent($payment, Gopay::METHOD_CARD_GPKB, function () {
			});
		}, GopayException::class, $exmsg);

		Assert::throws(function () use ($service, $payment) {
			$service->payRecurrentInline($payment, Gopay::METHOD_CARD_GPKB, function () {
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
		$service = new RecurrentPaymentService($gopay);

		$gopay->getSoap()->shouldReceive('voidRecurrentPayment')->once()->andReturnUsing(function () {
			Assert::truthy(true);
		});
		$service->cancelRecurrent(3000000001);

		$gopay->getSoap()->mockery_verify();
	}

	/**
	 * @test
	 * @return void
	 */
	public function testCancelRecurrentException()
	{
		$gopay = $this->createGopay();
		$exmsg = 'Fatal error during paying';
		$service = new RecurrentPaymentService($gopay);

		$gopay->getSoap()->shouldReceive('voidRecurrentPayment')->once()->andThrow('Exception', $exmsg);

		Assert::throws(function () use ($service) {
			$service->cancelRecurrent(3000000001);
		}, GopayException::class, $exmsg);

		$gopay->getSoap()->mockery_verify();
	}

}

$test = new RecurrentPaymentServiceTest();
$test->run();
