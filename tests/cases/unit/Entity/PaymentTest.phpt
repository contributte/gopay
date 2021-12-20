<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\Entity\Payment
 *
 * @testCase
 */

namespace Tests\Unit\Entity;

use Markette\Gopay\Entity\Payment;
use Markette\Gopay\Exception\InvalidArgumentException;
use Markette\Gopay\Gopay;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class PaymentTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testPayment()
	{
		$customer = [
			'firstName' => 'First',
			'lastName' => 'Last',
			'street' => 'Fake street 9',
			'city' => 'Prague',
			'postalCode' => '11000',
			'countryCode' => 'CZE',
			'email' => 'email@example.com',
			'phoneNumber' => '+420123456789',
			'undefined' => 'should be ignored',
		];

		$values = [
			'sum' => 999.12,
			'currency' => Gopay::CURRENCY_EUR,
			'variable' => 'string1234567890',
			'specific' => 6789012345,
			'productName' => 'Produkt',
			'customer' => $customer,
			'undefined' => 'should be ignored',
		];

		$payment = new Payment($values);

		Assert::equal(999.12, $payment->getSum());
		Assert::equal(99912, $payment->getSumInCents());
		Assert::equal(Gopay::CURRENCY_EUR, $payment->getCurrency());
		Assert::equal('string1234567890', $payment->getVariable());
		Assert::equal((int) 6789012345, $payment->getSpecific());
		Assert::equal('Produkt', $payment->getProductName());
		Assert::equal((object) [
			'firstName' => 'First',
			'lastName' => 'Last',
			'street' => 'Fake street 9',
			'city' => 'Prague',
			'postalCode' => '11000',
			'countryCode' => 'CZE',
			'email' => 'email@example.com',
			'phoneNumber' => '+420123456789',
		], $payment->getCustomer());
	}

	/**
	 * @test
	 * @return void
	 */
	public function testExceptions()
	{
		$payment = new Payment([]);
		Assert::exception(function () use ($payment) {
			$payment->setCurrency('NAN');
		}, InvalidArgumentException::class, 'Not supported currency "NAN".');
	}

}

$test = new PaymentTest();
$test->run();
