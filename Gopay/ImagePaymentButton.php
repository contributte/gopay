<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;

use Nette\Forms\Controls\ImageButton;


/**
 * Image payment button
 * 
 * @author     Vojtěch Dobeš
 * @subpackage Gopay
 *
 * @property-read $channel
 */
class ImagePaymentButton extends ImageButton
{

	/** @var string */
	private $channel;



	/**
	 * @param  string
	 * @param  string|NULL
	 * @param  string|NULL
	 */
	public function __construct($channel, $src = NULL, $alt = NULL)
	{
		parent::__construct($src, $alt);
		$this->channel = $channel;
		$this->control->title = $alt;
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
