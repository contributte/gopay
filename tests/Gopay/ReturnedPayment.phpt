<?php

/**
 * Test: ReturnPayment
 * @testCase
 */

use Tester\Assert;
use Markette\Gopay\ReturnedPayment;

require __DIR__ . '/../bootstrap.php';


class ReturnedPaymentTest extends BaseTest {
	
	public function testReturnedPayment() {
		$values = array('sum' => 999, 'customer' => array(), 'variable' => 7);
		$toBeVerified = array(
			'targetGoId' => 1234567890,
			'paymentSessionId' => 3000000001,
			'encryptedSignature' => '0bd97288ec61cb4485510f820a8a4772108e5799bd57b2df2173e088e0c7d419a0efb866855b27b7',
			'orderNumber' => 7
		);
		$returnedPayment = new ReturnedPayment($values, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', $toBeVerified);
		Assert::false($returnedPayment->isFraud());
	}
}


$test = new ReturnedPaymentTest();
$test->run();
