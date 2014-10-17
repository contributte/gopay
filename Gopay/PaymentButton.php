<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;

use Nette\Forms\Controls\SubmitButton;



/**
 * Payment button
 *
 * @author Vojtěch Dobeš
 *
 * @property-read $channel
 */
class PaymentButton extends SubmitButton implements IPaymentButton
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


	public function getChannel()
	{
		return $this->channel;
	}

}
