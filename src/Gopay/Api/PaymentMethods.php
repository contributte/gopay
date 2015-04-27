<?php

namespace Markette\Gopay\Api;


/**
 * Definice platebnich metod - stazeno pomoci WS ze serveru GoPay
 */
class PaymentMethods
{

	var $paymentMethods = array();

	public function adapt($paymentMethodsWS)
	{
		foreach ($paymentMethodsWS as $method) {
			$this->paymentMethods[] = new PaymentMethodElement(
				$method->code,
				$method->paymentMethod,
				$method->description,
				$method->logo,
				$method->offline
			);
		}
	}

}
