<?php

namespace Markette\Gopay\Service;

use Exception;
use Markette\Gopay\Api\GopayConfig;
use Markette\Gopay\Entity\RecurrentPayment;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Exception\GopayFatalException;
use Markette\Gopay\Exception\InvalidArgumentException;
use Nette\Application\Responses\RedirectResponse;

/**
 * RecurrentPayment Service
 */
class RecurrentPaymentService extends AbstractPaymentService
{

	/**
	 * Creates new RecurrentPayment with given default values
	 *
	 * @param array $values
	 * @return RecurrentPayment
	 */
	public function createPayment(array $values = [])
	{
		return new RecurrentPayment($values);
	}

	/**
	 * Executes payment via redirecting to GoPay payment gate
	 *
	 * @param RecurrentPayment $payment
	 * @param string $channel
	 * @param callable $callback
	 * @return RedirectResponse
	 * @throws InvalidArgumentException on undefined channel
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	public function payRecurrent(RecurrentPayment $payment, $channel, $callback)
	{
		$paymentSessionId = $this->buildRecurrentPayment($payment, $channel);

		$url = GopayConfig::fullIntegrationURL()
			. '?sessionInfo.targetGoId=' . $this->gopay->config->getGopayId()
			. '&sessionInfo.paymentSessionId=' . $paymentSessionId
			. '&sessionInfo.encryptedSignature=' . $this->createSignature($paymentSessionId);

		call_user_func_array($callback, [$paymentSessionId]);

		return new RedirectResponse($url);
	}

	/**
	 * Executes payment via INLINE GoPay payment gate
	 *
	 * @param RecurrentPayment $payment
	 * @param string $channel
	 * @param callable $callback
	 * @return array
	 * @throws InvalidArgumentException on undefined channel
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	public function payRecurrentInline(RecurrentPayment $payment, $channel, $callback)
	{
		$paymentSessionId = $this->buildRecurrentPayment($payment, $channel);

		$response = [
			'url' => GopayConfig::fullNewIntegrationURL() . '/' . $paymentSessionId,
			'signature' => $this->createSignature($paymentSessionId),
		];

		call_user_func_array($callback, [$paymentSessionId]);

		return $response;
	}

	/**
	 * Cancel recurrent payment via GoPay gateway
	 *
	 * @param float $paymentSessionId
	 * @throws GopayException
	 * @return void
	 */
	public function cancelRecurrent($paymentSessionId)
	{
		try {
			$this->gopay->soap->voidRecurrentPayment(
				$paymentSessionId,
				$this->gopay->config->getGopayId(),
				$this->gopay->config->getGopaySecretKey()
			);
		} catch (Exception $e) {
			throw new GopayException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Check and create recurrent payment
	 *
	 * @param RecurrentPayment $payment
	 * @param string $channel
	 * @return int
	 * @throws InvalidArgumentException on undefined channel or provided ReturnedPayment
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	protected function buildRecurrentPayment(RecurrentPayment $payment, $channel)
	{
		$channels = $this->getPaymentChannels($channel);

		try {
			$customer = $payment->getCustomer();
			$paymentSessionId = $this->gopay->soap->createRecurrentPayment(
				$this->gopay->config->getGopayId(),
				$payment->getProductName(),
				$payment->getSumInCents(),
				$payment->getCurrency(),
				$payment->getVariable(),
				$this->successUrl,
				$this->failureUrl,
				$payment->getRecurrenceDateTo(),
				$payment->getRecurrenceCycle(),
				$payment->getRecurrencePeriod(),
				$channels,
				$channel,
				$this->gopay->config->getGopaySecretKey(),
				$customer->firstName,
				$customer->lastName,
				$customer->city,
				$customer->street,
				$customer->postalCode,
				$customer->countryCode,
				$customer->email,
				$customer->phoneNumber,
				NULL,
				NULL,
				NULL,
				NULL,
				$this->lang
			);

			return $paymentSessionId;
		} catch (Exception $e) {
			throw new GopayException($e->getMessage(), 0, $e);
		}
	}

}
