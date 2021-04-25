<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\Entity\RecurrentPayment
 *
 * @testCase
 */

namespace Tests\Unit\Entity;

use Markette\Gopay\Entity\RecurrentPayment;
use Markette\Gopay\Exception\InvalidArgumentException;
use Markette\Gopay\Gopay;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class RecurrentPaymentTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testPayment()
	{
		$values = [
			'sum' => 999.12,
			'currency' => Gopay::CURRENCY_EUR,
		];

		$payment = new RecurrentPayment($values);

		Assert::equal(999.12, $payment->getSum());
		Assert::equal(99912.0, $payment->getSumInCents());
		Assert::equal(Gopay::CURRENCY_EUR, $payment->getCurrency());
	}

	/**
	 * @test
	 * @return void
	 */
	public function testRecurrentPayment()
	{
		$values = [
			'recurrenceCycle' => RecurrentPayment::PERIOD_DAY,
			'recurrenceDateTo' => '3000-01-01',
			'recurrencePeriod' => 10,
		];

		$payment = new RecurrentPayment($values);

		Assert::equal(RecurrentPayment::PERIOD_DAY, $payment->getRecurrenceCycle());
		Assert::equal('3000-01-01', $payment->getRecurrenceDateTo());
		Assert::equal(10, $payment->getRecurrencePeriod());
	}

	/**
	 * @test
	 * @return void
	 */
	public function testExceptions()
	{
		Assert::exception(function () {
			new RecurrentPayment(['recurrenceCycle' => 'x']);
		}, InvalidArgumentException::class, 'Not supported cycle "x".');
	}

}

$test = new RecurrentPaymentTest();
$test->run();
