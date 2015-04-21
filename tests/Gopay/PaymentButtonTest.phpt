<?php

/**
 * Test: PaymentButton
 *
 * @testCase
 */

use Markette\Gopay\PaymentButton;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class PaymentButtonTest extends BaseTestCase
{

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
