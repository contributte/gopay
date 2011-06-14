<?php

/**
 * Gopay Wrapper
 * 
 * @author Vojtech Dobes
 */

namespace Gopay;

use GopayHelper;
use GopaySoap;

/**
 * Representation of payment
 * 
 * @author  Vojtech Dobes
 * @package Gopay Wrapper
 */
class ReturnedPayment extends Payment
{
	
	/** @var \Gopay\Helper */
	private $gopay;
	
	/** @var \stdClass */
	private $gopayIdentification;
	
	/** @var int */
	private $id;
	
/* === Description ========================================================== */	
	
	/** @var int */
	private $sum;
	
	/** @var int */
	private $variable;
	
	/** @var int */
	private $specific;
	
	/** @var string */
	private $product;

	/** @var \stdClass */
	private $customer;
	
/* === Verification ========================================================= */
	
	/** @var array */
	private $valuesToBeVerified = array();

	/**
	 * @param  \Gopay\Helper $gopay
	 * @param  \stdClass $identification
	 * @param  array $values
	 * @param  array $valuesToBeVerified
	 */
	public function __construct(Helper $gopay, \stdClass $identification, $values, array $valuesToBeVerified = array())
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

	const FAILURE_SUPERCASH = -3,
		FAILURE_BANK        = -7;

	/** @var int */
	private $failureInfo;

	/**
	 * Returns TRUE if payment is verified by Gopay as paid
	 *
	 * @return bool
	 */
	public function isPaid()
	{
		$this->failureInfo = GopaySoap::isEshopPaymentDone(
			$this->valuesToBeVerified['paymentSessionId'],
			$this->gopayIdentification->id,
			$this->variable,
			$this->sum,
			$this->product,
			$this->gopayIdentification->secretKey
		);

		return $this->failureInfo === 1;
	}


}
