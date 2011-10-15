<?php

/**
 * Gopay Wrapper
 * 
 * @author Vojtech Dobes
 */

namespace Gopay;

use Nette\Forms\Controls\SubmitButton;


/**
 * Payment button
 *
 * @package       Gopay Wrapper
 * @property-read $channel
 */
class PaymentButton extends SubmitButton
{

	/** @var string */
	private $channel;


	/**
	 * @param  string
	 * @param  string|NULL
	 */
	public function __construct($channel, $caption = NULL)
	{
		parent::__construct($caption);
		$this->channel = $channel;
	}


	/**
	 * Returns name of payment channel
	 *
	 * @return string
	 */
	public function getChannel()
	{
		return $this->channel;
	}

}
