<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\Entity\ReturnPayment
 *
 * @testCase
 */

namespace Tests\Unit\Entity;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Markette\Gopay\Config;
use Markette\Gopay\Entity\ReturnedPayment;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Gopay;
use Mockery;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class ReturnedPaymentTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testNoGopay()
	{
		$toBeVerified = [
			'targetGoId' => 1,
			'paymentSessionId' => 2,
			'encryptedSignature' => 3,
			'orderNumber' => 4,
		];
		$returnedPayment = new ReturnedPayment([], $toBeVerified);

		Assert::throws(function () use ($returnedPayment) {
			$returnedPayment->isFraud();
		}, GopayException::class, 'No gopay set');
	}

	/**
	 * @test
	 * @return void
	 */
	public function testFraud()
	{
		$config = Mockery::namedMock('Config1', Config::class);
		$config->shouldReceive('getGopayId')->once()->andReturn(1);
		$config->shouldReceive('getGopaySecretKey')->once()->andReturn(1);

		$soap = Mockery::namedMock('GopaySoap1', GopaySoap::class);

		$helper = Mockery::namedMock('GopayHelper1', GopayHelper::class);
		$helper->shouldReceive('checkPaymentIdentity')->once()->andThrow('Exception');

		$gopay = new Gopay($config, $soap, $helper);

		$toBeVerified = [
			'targetGoId' => 1,
			'paymentSessionId' => 2,
			'encryptedSignature' => 3,
			'orderNumber' => 4,
		];
		$returnedPayment = new ReturnedPayment([], $toBeVerified);
		$returnedPayment->setGopay($gopay);

		Assert::true($returnedPayment->isFraud());

		$config->mockery_verify();
		$helper->mockery_verify();
	}

	/**
	 * @test
	 * @return void
	 */
	public function testFraudFalse()
	{
		$config = Mockery::namedMock('Config2', Config::class);
		$config->shouldReceive('getGopayId')->once()->andReturn(1);
		$config->shouldReceive('getGopaySecretKey')->once()->andReturn(1);

		$soap = Mockery::namedMock('GopaySoap2', GopaySoap::class);

		$helper = Mockery::namedMock('GopayHelper2', GopayHelper::class);
		$helper->shouldReceive('checkPaymentIdentity')->once()->andReturnNull();

		$gopay = new Gopay($config, $soap, $helper);

		$toBeVerified = [
			'targetGoId' => 1,
			'paymentSessionId' => 2,
			'encryptedSignature' => 3,
			'orderNumber' => 4,
		];
		$returnedPayment = new ReturnedPayment([], $toBeVerified);
		$returnedPayment->setGopay($gopay);

		Assert::false($returnedPayment->isFraud());

		$config->mockery_verify();
		$helper->mockery_verify();
	}

	/**
	 * @test
	 * @return void
	 */
	public function testGetStatus()
	{
		$result = ['sessionState' => GopayHelper::CANCELED];
		$config = Mockery::namedMock('Config3', Config::class);
		$config->shouldReceive('getGopayId')->once()->andReturn(1);
		$config->shouldReceive('getGopaySecretKey')->once()->andReturn(1);

		$soap = Mockery::namedMock('GopaySoap3', GopaySoap::class);
		$soap->shouldReceive('isPaymentDone')->once()->andReturn($result);

		$helper = Mockery::namedMock('GopayHelper3', GopayHelper::class);

		$gopay = new Gopay($config, $soap, $helper);

		$returnedPayment = new ReturnedPayment([], ['paymentSessionId' => 1]);
		$returnedPayment->setGopay($gopay);

		$status = $returnedPayment->getStatus();
		Assert::same($result, $status);
		Assert::equal($status, $status);

		$soap->mockery_verify();
	}

	/**
	 * @test
	 * @return void
	 */
	public function testStatuses()
	{
		$result = ['sessionState' => GopayHelper::CANCELED];
		$config = Mockery::namedMock('Config4', Config::class);
		$config->shouldReceive('getGopayId')->once()->andReturn(1);
		$config->shouldReceive('getGopaySecretKey')->once()->andReturn(1);

		$soap = Mockery::namedMock('GopaySoap4', GopaySoap::class);
		$soap->shouldReceive('isPaymentDone')->once()->andReturn($result);

		$helper = Mockery::namedMock('GopayHelper4', GopayHelper::class);

		$gopay = new Gopay($config, $soap, $helper);

		$returnedPayment = new ReturnedPayment([], ['paymentSessionId' => 1]);
		$returnedPayment->setGopay($gopay);

		Assert::false($returnedPayment->isAuthorized());
		Assert::false($returnedPayment->isPaid());
		Assert::false($returnedPayment->isRefunded());
		Assert::false($returnedPayment->isTimeouted());
		Assert::false($returnedPayment->isWaiting());

		Assert::true($returnedPayment->isCanceled());

		$soap->mockery_verify();
	}

}

$test = new ReturnedPaymentTest();
$test->run();
