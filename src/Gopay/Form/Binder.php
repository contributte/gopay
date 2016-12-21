<?php

namespace Markette\Gopay\Form;

use Markette\Gopay\Exception\InvalidArgumentException;
use Markette\Gopay\Service\AbstractPaymentService;
use Nette\Forms\Container;
use stdClass;

class Binder
{

	/**
	 * Binds payment buttons fo form
	 *
	 * @param AbstractPaymentService $service
	 * @param Container $container
	 * @param array|callable $callbacks
	 * @return void
	 */
	public function bindPaymentButtons(AbstractPaymentService $service, Container $container, $callbacks)
	{
		foreach ($service->getChannels() as $channel) {
			$this->bindPaymentButton($channel, $container, $callbacks);
		}
	}

	/**
	 * Binds form to Gopay
	 * - adds one payment button for given channel
	 *
	 * @param stdClass $channel
	 * @param Container $container
	 * @param array|callable $callbacks
	 * @throws InvalidArgumentException
	 * @return IPaymentButton
	 */
	public function bindPaymentButton($channel, Container $container, $callbacks = [])
	{
		if (!isset($channel->logo)) {
			$button = $container['gopayChannel' . $channel->code] = new PaymentButton($channel->code, $channel->name);
		} else {
			$button = $container['gopayChannel' . $channel->code] = new ImagePaymentButton($channel->code, $channel->logo, $channel->name);
		}

		$channel->control = 'gopayChannel' . $channel->code;

		if (!is_array($callbacks)) $callbacks = [$callbacks];
		foreach ($callbacks as $callback) {
			$button->onClick[] = $callback;
		}

		return $button;
	}

}
