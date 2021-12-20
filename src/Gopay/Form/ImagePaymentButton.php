<?php declare(strict_types = 1);

namespace Markette\Gopay\Form;

use Nette\Forms\Controls\ImageButton;

/**
 * Image payment button
 *
 * @property-read string $channel
 */
class ImagePaymentButton extends ImageButton implements IPaymentButton
{

	/** @var string */
	private $channel;

	public function __construct(string $channel, ?string $src = null, ?string $alt = null)
	{
		parent::__construct($src, $alt);
		$this->channel = $channel;
		$this->control->title = $alt;
	}

	public function getChannel(): string
	{
		return $this->channel;
	}

}
