<?php declare(strict_types = 1);

namespace Markette\Gopay\Service;

use Markette\Gopay\Api\GopayConfig;
use Markette\Gopay\Entity\PreAuthorizedPayment;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Exception\GopayFatalException;
use Markette\Gopay\Exception\InvalidArgumentException;
use Nette\Application\Responses\RedirectResponse;
use Throwable;

/**
 * PreAuthorizedPayment Service
 */
class PreAuthorizedPaymentService extends AbstractPaymentService
{

	/**
	 * Creates new PreAuthorizedPayment with given default values
	 *
	 * @param array $values
	 */
	public function createPayment(array $values = []): PreAuthorizedPayment
	{
		return new PreAuthorizedPayment($values);
	}

	/**
	 * Executes payment via redirecting to GoPay payment gate
	 *
	 * @throws InvalidArgumentException on undefined channel
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	public function payPreAuthorized(PreAuthorizedPayment $payment, string $channel, callable $callback): RedirectResponse
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
	 * @return array
	 * @throws InvalidArgumentException on undefined channel
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	public function payPreAuthorizedInline(PreAuthorizedPayment $payment, string $channel, callable $callback): array
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
	 * @throws GopayException
	 */
	public function capturePreAuthorized($paymentSessionId): void
	{
		try {
			$this->gopay->soap->capturePayment(
				$paymentSessionId,
				$this->gopay->config->getGopayId(),
				$this->gopay->config->getGopaySecretKey()
			);
		} catch (Throwable $e) {
			throw new GopayException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Cancel pre authorized payment via GoPay gateway
	 *
	 * @throws GopayException
	 */
	public function cancelPreAuthorized($paymentSessionId): void
	{
		try {
			$this->gopay->soap->voidAuthorization(
				$paymentSessionId,
				$this->gopay->config->getGopayId(),
				$this->gopay->config->getGopaySecretKey()
			);
		} catch (Throwable $e) {
			throw new GopayException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Check and create pre authorized payment
	 *
	 * @throws InvalidArgumentException on undefined channel or provided ReturnedPayment
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	protected function buildPreAuthorizedPayment(PreAuthorizedPayment $payment, string $channel)
	{
		/** @var string $channels */
		$channels = $this->getPaymentChannels($channel);

		try {
			$customer = $payment->getCustomer();
			return $this->gopay->soap->createPreAutorizedPayment(
				(string) $this->gopay->config->getGopayId(),
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
				'',
				'',
				'',
				'',
				$this->lang
			);
		} catch (Throwable $e) {
			throw new GopayException($e->getMessage(), 0, $e);
		}
	}

}
