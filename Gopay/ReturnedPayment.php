<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;

use GopayHelper;
use GopaySoap;
use stdClass;


/**
 * Representation of payment returned from Gopay Payment Gate
 *
 * @author     Vojtěch Dobeš
 * @subpackage Gopay
 */
class ReturnedPayment extends Payment
{

	/** @var array */
	private $valuesToBeVerified = array();



	/**
	 * @param  Service
	 * @param  stdClass
	 * @param  array
	 * @param  array
	 */
	public function __construct(Service $gopay, stdClass $identification, $values, array $valuesToBeVerified = array())
	{
		parent::__construct($gopay, $identification, $values);
		$this->valuesToBeVerified = $valuesToBeVerified;
	}



/* === Security ============================================================= */



	/**
	 * Returns TRUE if payment is declared fraud by Gopay
	 *
	 * @return bool
	 */
	public function isFraud()
	{
		error_reporting(E_ALL ^ E_NOTICE);

		return GopayHelper::checkPaymentIdentity(
			$this->valuesToBeVerified['eshopGoId'],
			$this->valuesToBeVerified['paymentSessionId'],
			$this->valuesToBeVerified['variableSymbol'],
			$this->valuesToBeVerified['encryptedSignature'],
			$this->gopayIdentification->id,
			$this->variable,
			$this->gopayIdentification->secretKey
		);
	}



/* === Status =============================================================== */



	/** @const int */
	const FAILURE_SUPERCASH = -3,
		FAILURE_BANK        = -7;

	/** @var array */
	private $result;



	/**
	 * Returns TRUE if payment is verified by Gopay as paid
	 *
	 * @return bool
	 */
	public function isPaid()
	{
		$this->getStatus();
		return $this->result['code'] === GopayHelper::PAYMENT_DONE;
	}



	/**
	 * Returns TRUE if payment is waiting to be paid
	 *
	 * @return bool
	 */
	public function isWaiting()
	{
		$this->getStatus();
		return $this->result['code'] === GopayHelper::WAITING;
	}



	/**
	 * Returns TRUE if payment is canceled
	 *
	 * @return bool
	 */
	public function isCanceled()
	{
		$this->getStatus();
		return $this->result['code'] === GopayHelper::CANCELED;
	}



	/**
	 * Returns TRUE if payment time limit already expired
	 *
	 * @return bool
	 */
	public function isTimeouted()
	{
		$this->getStatus();
		return $this->result['code'] === GopayHelper::CANCELED;
	}



	/**
	 * Returns description of payment status received from Gopay WS
	 *
	 * @return string
	 */
	public function getDescription()
	{
		$this->getStatus();
		return $this->result['description'];
	}



	/**
	 * Receives status of payment from Gopay WS
	 *
	 * @return array
	 */
	public function getStatus()
	{
		if ($this->result !== NULL) return $this->result;
		return $this->result = GopaySoap::isEshopPaymentDone(
			$this->valuesToBeVerified['paymentSessionId'],
			$this->gopayIdentification->id,
			$this->variable,
			$this->sum,
			$this->product,
			$this->gopayIdentification->secretKey
		);
	}

}
