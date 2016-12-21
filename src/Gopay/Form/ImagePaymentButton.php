<?php

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

	/**
	 * @param string $channel
	 * @param string $src
	 * @param string $alt
	 */
	public function __construct($channel, $src = NULL, $alt = NULL)
	{
		parent::__construct($src, $alt);
		$this->channel = $channel;
		$this->control->title = $alt;
	}

	/**
	 * @return string
	 */
	public function getChannel()
	{
		return $this->channel;
	}

}
