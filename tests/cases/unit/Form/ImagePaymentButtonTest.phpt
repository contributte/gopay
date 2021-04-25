<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\Form\ImagePaymentButton
 *
 * @testCase
 */

namespace Tests\Unit\Form;

use Markette\Gopay\Form\ImagePaymentButton;
use Nette\Forms\Form;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class ImagePaymentButtonTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
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
