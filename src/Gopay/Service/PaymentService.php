<?php declare(strict_types = 1);

namespace Markette\Gopay\Service;

use Markette\Gopay\Api\GopayConfig;
use Markette\Gopay\Entity\Payment;
use Markette\Gopay\Entity\ReturnedPayment;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Exception\GopayFatalException;
use Markette\Gopay\Exception\InvalidArgumentException;
use Nette\Application\Responses\RedirectResponse;
use Throwable;

/**
 * Payment Service
 */
class PaymentService extends AbstractPaymentService
{

	/**
	 * Creates new Payment with given default values
	 *
	 * @param array $values
	 */
	public function createPayment(array $values = []): Payment
	{
		return new Payment($values);
	}

	/**
	 * Executes payment via redirecting to GoPay payment gate
	 *
	 * @throws InvalidArgumentException on undefined channel
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	public function pay(Payment $payment, string $channel, callable $callback): RedirectResponse
	{
		$paymentSessionId = $this->buildPayment($payment, $channel);

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
	public function payInline(Payment $payment, string $channel, callable $callback): array
	{
		$paymentSessionId = $this->buildPayment($payment, $channel);

		$response = [
			'url' => GopayConfig::fullNewIntegrationURL() . '/' . $paymentSessionId,
			'signature' => $this->createSignature($paymentSessionId),
		];

		call_user_func_array($callback, [$paymentSessionId]);

		return $response;
	}

	/**
	 * Check and create payment
	 *
	 * @throws InvalidArgumentException on undefined channel or provided ReturnedPayment
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	protected function buildPayment(Payment $payment, string $channel)
	{
		if ($payment instanceof ReturnedPayment) {
			throw new InvalidArgumentException("Cannot use instance of 'ReturnedPayment'! This payment has been already used for paying");
		}

		/** @var string $channels */
		$channels = $this->getPaymentChannels($channel);

		try {
			$customer = $payment->getCustomer();
			return $this->gopay->soap->createPayment(
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
