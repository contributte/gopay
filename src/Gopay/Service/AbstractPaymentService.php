<?php

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

	/**
	 * @param Gopay $gopay
	 */
	public function __construct(Gopay $gopay)
	{
		$this->gopay = $gopay;
	}

	/**
	 * Returns payment after visiting Payment Gate
	 *
	 * @param array $values
	 * @param array $valuesToBeVerified
	 * @return ReturnedPayment
	 */
	public function restorePayment(array $values, array $valuesToBeVerified)
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
	 * @return BasePayment
	 */
	abstract public function createPayment(array $values);

	/**
	 * HELPERS *****************************************************************
	 */

	/**
	 * @param string $channel
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected function getPaymentChannels($channel)
	{
		if (!isset($this->channels[$channel]) && $channel !== Gopay::METHOD_USER_SELECT) {
			throw new InvalidArgumentException(sprintf('Payment channel \'%s\' is not supported', $channel));
		}

		if ($this->changeChannel === TRUE) {
			$channels = array_keys($this->channels);
		} else {
			$channels = [$channel];
		}

		return $channels;
	}

	/**
	 * Creates encrypted signature for given given payment session id
	 *
	 * @param int $paymentSessionId
	 * @return string
	 */
	protected function createSignature($paymentSessionId)
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
