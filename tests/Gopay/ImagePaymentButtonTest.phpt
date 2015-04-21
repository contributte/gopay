<?php

/**
 * Test: ImagePaymentButton
 *
 * @testCase
 */

use Markette\Gopay\ImagePaymentButton;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class ImagePaymentButtonTest extends BaseTestCase
{

    public function testImagePaymentButton()
    {
        $channel = 'channel_1';
        $src = 'src_2';
        $alt = 'alt_3';
        $form = new Form();
        $form['imageButton'] = $button = new ImagePaymentButton($channel, $src, $alt);

        Assert::same($channel, $button->getChannel());
        Assert::same($src, $button->control->src);
        Assert::same($alt, $button->control->alt);
    }
}

$test = new ImagePaymentButtonTest();
$test->run();
