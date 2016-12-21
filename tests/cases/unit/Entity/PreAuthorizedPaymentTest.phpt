<?php

/**
 * Test: Markette\Gopay\Entity\PreAuthorizedPayment
 *
 * @testCase
 */

namespace Tests\Unit\Entity;

use Markette\Gopay\Entity\PreAuthorizedPayment;
use Markette\Gopay\Gopay;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class PreAuthorizedPaymentTest extends BaseTestCase
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

		$payment = new PreAuthorizedPayment($values);

		Assert::equal(999.12, $payment->getSum());
		Assert::equal(99912.0, $payment->getSumInCents());
		Assert::equal(Gopay::CURRENCY_EUR, $payment->getCurrency());
	}

}

$test = new PreAuthorizedPaymentTest();
$test->run();
