<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\DI\Extension
 *
 * @testCase
 */

namespace Tests\Unit\DI;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Markette\Gopay\Config;
use Markette\Gopay\Gopay;
use Markette\Gopay\Service\PaymentService;
use Markette\Gopay\Service\PreAuthorizedPaymentService;
use Markette\Gopay\Service\RecurrentPaymentService;
use ReflectionProperty;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class ExtensionTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testServiceCreation()
	{
		$container = $this->createContainer(FIXTURES_DIR . '/config/default.neon');

		Assert::type(GopaySoap::class, $container->getService('gopay.driver'));
		Assert::type(GopayHelper::class, $container->getService('gopay.helper'));
		Assert::type(Config::class, $container->getService('gopay.config'));
		Assert::type(Gopay::class, $container->getService('gopay.gopay'));

		Assert::type(PaymentService::class, $container->getService('gopay.service.payment'));
		Assert::type(RecurrentPaymentService::class, $container->getService('gopay.service.recurrentPayment'));
		Assert::type(PreAuthorizedPaymentService::class, $container->getService('gopay.service.preAuthorizedPayment'));
	}

	/**
	 * @test
	 * @return void
	 */
	public function testChannels()
	{
		$container = $this->createContainer(FIXTURES_DIR . '/config/channels.neon');
		$paymentService = $container->getService('gopay.service.payment');

		Assert::equal(
			[
				'eu_gp_u' => (object) [
					'code' => 'eu_gp_u',
					'name' => 'Platba kartou - Česká spořitelna',
					'logo' => null,
					'offline' => null,
					'description' => null,
				],
				'eu_bank' => (object) [
					'code' => 'eu_bank',
					'name' => 'Běžný bankovní převod',
					'logo' => null,
					'offline' => null,
					'description' => null,
				],
				'SUPERCASH' => (object) [
					'code' => 'SUPERCASH',
					'name' => 'Terminál České pošty',
					'logo' => null,
					'offline' => null,
					'description' => null,
				],
				'cz_kb' => (object) [
					'code' => 'cz_kb',
					'name' => 'Platba KB - Mojeplatba',
					'logo' => null,
					'offline' => null,
					'description' => null,
				],
				'sk_otpbank' => (object) [
					'code' => 'sk_otpbank',
					'name' => 'Platba OTP banka Slovensko, a.s.',
					'logo' => 'opt-logo.png',
					'offline' => null,
					'description' => null,
				],
			],
			$paymentService->getChannels()
		);
	}

	/**
	 * @test
	 * @return void
	 */
	public function testChangeChannel()
	{
		$property = new ReflectionProperty(PaymentService::class, 'changeChannel');
		$property->setAccessible(true);

		$container = $this->createContainer([
			'gopay' => [
				'payments' => [
					'changeChannel' => false,
				],
			],
		]);

		$service = $container->getService('gopay.service.payment');
		Assert::false($property->getValue($service));

		$container = $this->createContainer([
			'gopay' => [
				'payments' => [
					'changeChannel' => true,
				],
			],
		]);

		$service = $container->getService('gopay.service.payment');
		Assert::true($property->getValue($service));
	}

}

$test = new ExtensionTest();
$test->run();
