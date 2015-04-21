<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;



/**
 * Representation of payment returned from Gopay Payment Gate
 *
 * @author Vojtěch Dobeš
 * @author Jan Skrasek
 */
class ReturnedPayment extends Payment
{

	/** @var float */
	private $gopayId;

	/** @var string */
	private $gopaySecretKey;

	/** @var array */
	private $valuesToBeVerified = array();

	/** @var array */
	private $result;

	/** @var GopayHolder */
	private $holder;


	/**
	 * @param  Service
	 * @param  stdClass
	 * @param  array
	 * @param  array
	 */
	public function __construct(array $values, $gopayId, $gopaySecretKey, array $valuesToBeVerified = array())
	{
		parent::__construct($values);
		$this->gopayId = (float) $gopayId;
		$this->gopaySecretKey = (string) $gopaySecretKey;
		$this->valuesToBeVerified = $valuesToBeVerified;
		$this->holder = GopayHolder::getInstance();
	}



	/**
	 * Returns TRUE if payment is declared fraud by Gopay
	 *
	 * @return bool
	 */
	public function isFraud()
	{
		try {
			$this->holder->getHelper()->checkPaymentIdentity(
				(float) $this->valuesToBeVerified['targetGoId'],
				(float) $this->valuesToBeVerified['paymentSessionId'],
				null,
				$this->valuesToBeVerified['orderNumber'],
				$this->valuesToBeVerified['encryptedSignature'],
				(float) $this->gopayId,
				$this->getVariable(),
				$this->gopaySecretKey
			);
			return FALSE;
		} catch (\Exception $e) {
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

		return $this->result = $this->holder->getSoap()->isPaymentDone(
			(float) $this->valuesToBeVerified['paymentSessionId'],
			(float) $this->gopayId,
			$this->getVariable(),
			(int) $this->getSumInCents(),
			$this->getCurrency(),
			$this->getProductName(),
			$this->gopaySecretKey
		);
	}

}
