<?php

namespace Markette\Gopay\Service;

use Exception;
use Markette\Gopay\Api\GopayConfig;
use Markette\Gopay\Entity\PreAuthorizedPayment;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Exception\GopayFatalException;
use Markette\Gopay\Exception\InvalidArgumentException;
use Nette\Application\Responses\RedirectResponse;

/**
 * PreAuthorizedPayment Service
 */
class PreAuthorizedPaymentService extends AbstractPaymentService
{

	/**
	 * Creates new PreAuthorizedPayment with given default values
	 *
	 * @param array $values
	 * @return PreAuthorizedPayment
	 */
	public function createPayment(array $values = [])
	{
		return new PreAuthorizedPayment($values);
	}

	/**
	 * Executes payment via redirecting to GoPay payment gate
	 *
	 * @param PreAuthorizedPayment $payment
	 * @param string $channel
	 * @param callable $callback
	 * @return RedirectResponse
	 * @throws InvalidArgumentException on undefined channel
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	public function payPreAuthorized(PreAuthorizedPayment $payment, $channel, $callback)
	{
		$paymentSessionId = $this->buildPreAuthorizedPayment($payment, $channel);

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
	 * @param PreAuthorizedPayment $payment
	 * @param string $channel
	 * @param callable $callback
	 * @return array
	 * @throws InvalidArgumentException on undefined channel
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	public function payPreAuthorizedInline(PreAuthorizedPayment $payment, $channel, $callback)
	{
		$paymentSessionId = $this->buildPreAuthorizedPayment($payment, $channel);

		$response = [
			'url' => GopayConfig::fullNewIntegrationURL() . '/' . $paymentSessionId,
			'signature' => $this->createSignature($paymentSessionId),
		];

		call_user_func_array($callback, [$paymentSessionId]);

		return $response;
	}

	/**
	 * Capture pre authorized payment via GoPay gateway
	 *
	 * @param float $paymentSessionId
	 * @throws GopayException
	 * @return void
	 */
	public function capturePreAuthorized($paymentSessionId)
	{
		try {
			$this->gopay->soap->capturePayment(
				$paymentSessionId,
				$this->gopay->config->getGopayId(),
				$this->gopay->config->getGopaySecretKey()
			);
		} catch (Exception $e) {
			throw new GopayException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Cancel pre authorized payment via GoPay gateway
	 *
	 * @param float $paymentSessionId
	 * @throws GopayException
	 * @return void
	 */
	public function cancelPreAuthorized($paymentSessionId)
	{
		try {
			$this->gopay->soap->voidAuthorization(
				$paymentSessionId,
				$this->gopay->config->getGopayId(),
				$this->gopay->config->getGopaySecretKey()
			);
		} catch (Exception $e) {
			throw new GopayException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Check and create pre authorized payment
	 *
	 * @param PreAuthorizedPayment $payment
	 * @param string $channel
	 * @return int
	 * @throws InvalidArgumentException on undefined channel or provided ReturnedPayment
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	protected function buildPreAuthorizedPayment(PreAuthorizedPayment $payment, $channel)
	{
		$channels = $this->getPaymentChannels($channel);

		try {
			$customer = $payment->getCustomer();
			$paymentSessionId = $this->gopay->soap->createPreAutorizedPayment(
				$this->gopay->config->getGopayId(),
				$payment->getProductName(),
				$payment->getSumInCents(),
				$payment->getCurrency(),
				$payment->getVariable(),
				$this->successUrl,
				$this->failureUrl,
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
