<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\Form\PaymentButton
 *
 * @testCase
 */

namespace Tests\Unit\Form;

use Markette\Gopay\Form\PaymentButton;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class PaymentButtonTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testPaymentButton()
	{
		$channel = 'channel_1';
		$caption = 'caption_2';
		$button = new PaymentButton($channel, $caption);

		Assert::same($channel, $button->getChannel());
		Assert::same($caption, $button->caption);
	}

}

$test = new PaymentButtonTest();
$test->run();
