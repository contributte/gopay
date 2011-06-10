<?php

/**
 * Gopay Helper with Happy API
 * 
 * @author Vojtech Dobes
 */

namespace VojtechDobes\Gopay;

use Nette\Forms\SubmitButton;

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
