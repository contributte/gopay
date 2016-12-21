<?php

namespace Markette\Gopay\Form;

/**
 * Payment button interface
 */
interface IPaymentButton
{

	/**
	 * Returns name (title) of payment channel.
	 *
	 * @return string
	 */
	public function getChannel();

}
