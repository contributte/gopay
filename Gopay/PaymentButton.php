<?php

/**
 * Gopay Helper with Happy API
 * 
 * @author Vojtech Dobes
 */

namespace Gopay;

use Nette\Forms\Controls\SubmitButton;

/**
 * Payment button
 *
 * @property-read   $channel
 */
class PaymentButton extends SubmitButton
{
	
	/** @var string */
	private $channel;
	
	public function setChannel($channel)
	{
		$this->channel = $channel;
	}
	
	public function getChannel()
	{
		return $this->channel;
	}


}
