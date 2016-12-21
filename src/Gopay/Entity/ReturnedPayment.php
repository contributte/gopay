<?php

namespace Markette\Gopay\Entity;

use Exception;
use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Exception\GopayFatalException;
use Markette\Gopay\Gopay;

/**
 * Representation of payment returned from Gopay Payment Gate
 */
class ReturnedPayment extends Payment
{

	/** @var array */
	private $valuesToBeVerified = [];

	/** @var array */
	private $result;

	/** @var Gopay */
	private $gopay;

	/**
	 * @param array $values
	 * @param array $valuesToBeVerified
	 */
	public function __construct(array $values, array $valuesToBeVerified)
	{
		parent::__construct($values);
		$this->valuesToBeVerified = $valuesToBeVerified;
	}

	/**
	 * @param Gopay $gopay
	 * @return void
	 */
	public function setGopay(Gopay $gopay)
	{
		$this->gopay = $gopay;
	}

	/**
	 * @return Gopay
	 * @throws GopayException
	 */
	protected function getGopay()
	{
		if (!$this->gopay) {
			throw new GopayException('No gopay set');
		}

		return $this->gopay;
	}

	/**
	 * Returns TRUE if payment is declared fraud by Gopay
	 *
	 * @return bool
	 * @throws GopayFatalException
	 */
	public function isFraud()
	{
		try {
			$this->getGopay()->getHelper()->checkPaymentIdentity(
				(float) $this->valuesToBeVerified['targetGoId'],
				(float) $this->valuesToBeVerified['paymentSessionId'],
				NULL,
				$this->valuesToBeVerified['orderNumber'],
				$this->valuesToBeVerified['encryptedSignature'],
				(float) $this->getGopay()->getConfig()->getGopayId(),
				$this->getVariable(),
				$this->getGopay()->getConfig()->getGopaySecretKey()
			);

			return FALSE;
		} catch (GopayFatalException $e) {
			throw $e;
		} catch (Exception $e) {
			return TRUE;
		}
	}

	/**
	 * Returns TRUE if payment is verified by Gopay as paid
	 *
	 * @return bool
	 */
	public function isPaid()
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::PAID;
	}

	/**
	 * Returns TRUE if payment is waiting to be paid
	 *
	 * @return bool
	 */
	public function isWaiting()
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::PAYMENT_METHOD_CHOSEN;
	}

	/**
	 * Returns TRUE if payment is canceled
	 *
	 * @return bool
	 */
	public function isCanceled()
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::CANCELED;
	}

	/**
	 * Returns TRUE if payment is refunded
	 *
	 * @return bool
	 */
	public function isRefunded()
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::REFUNDED;
	}

	/**
	 * Returns TRUE if payment is authorized
	 *
	 * @return bool
	 */
	public function isAuthorized()
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::AUTHORIZED;
	}

	/**
	 * Returns TRUE if payment time limit already expired
	 *
	 * @return bool
	 */
	public function isTimeouted()
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::TIMEOUTED;
	}

	/**
	 * Receives status of payment from Gopay WS
	 *
	 * @return array
	 */
	public function getStatus()
	{
		if ($this->result !== NULL) {
			return $this->result;
		}

		return $this->result = $this->getGopay()->getSoap()->isPaymentDone(
			(float) $this->valuesToBeVerified['paymentSessionId'],
			(float) $this->getGopay()->getConfig()->getGopayId(),
			$this->getVariable(),
			(int) $this->getSumInCents(),
			$this->getCurrency(),
			$this->getProductName(),
			$this->getGopay()->getConfig()->getGopaySecretKey()
		);
	}

}
