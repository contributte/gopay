<?php declare(strict_types = 1);

namespace Markette\Gopay\Service;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Entity\BasePayment;
use Markette\Gopay\Entity\ReturnedPayment;
use Markette\Gopay\Exception\InvalidArgumentException;
use Markette\Gopay\Gopay;

abstract class AbstractPaymentService extends AbstractService
{

	/** @var Gopay */
	protected $gopay;

	public function __construct(Gopay $gopay)
	{
		$this->gopay = $gopay;
	}

	/**
	 * Returns payment after visiting Payment Gate
	 *
	 * @param array $values
	 * @param array $valuesToBeVerified
	 */
	public function restorePayment(array $values, array $valuesToBeVerified): ReturnedPayment
	{
		$payment = new ReturnedPayment($values, $valuesToBeVerified);
		$payment->setGopay($this->gopay);

		return $payment;
	}

	/**
	 * ABSTRACT ****************************************************************
	 */

	/**
	 * Create Payment
	 *
	 * @param array $values
	 */
	abstract public function createPayment(array $values): BasePayment;

	/**
	 * HELPERS *****************************************************************
	 */

	/**
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected function getPaymentChannels(string $channel): array
	{
		if (!isset($this->channels[$channel]) && $channel !== Gopay::METHOD_USER_SELECT) {
			throw new InvalidArgumentException(sprintf('Payment channel \'%s\' is not supported', $channel));
		}

		if ($this->changeChannel === true) {
			$channels = array_keys($this->channels);
		} else {
			$channels = [$channel];
		}

		return $channels;
	}

	/**
	 * Creates encrypted signature for given given payment session id
	 */
	protected function createSignature(int $paymentSessionId): string
	{
		return GopayHelper::encrypt(
			GopayHelper::hash(
				GopayHelper::concatPaymentSession(
					$this->gopay->config->getGopayId(),
					(float) $paymentSessionId,
					$this->gopay->config->getGopaySecretKey()
				)
			),
			$this->gopay->config->getGopaySecretKey()
		);
	}

}
