<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;


interface IPaymentButton
{

	/**
	 * Returns name (title) of payment channel.
	 * @return string
	 */
	function getChannel();

}
