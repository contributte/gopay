<?php

/**
 * Gopay Helper with Happy API
 * 
 * 
 * @author Vojtech Dobes
 */

namespace VojtechDobes\Gopay;

use GopayHelper;
use GopaySoap;

use Nette\Object;
use Nette\Application\RedirectingResponse;

use InvalidArgumentException;

/**
 * Gopay helper with simple API
 * 
 * @author Vojtech Dobes
 */
class Helper extends Object
{
	
	/** @const string */
	const SUPERCASH = GopayHelper::SUPERCASH,
		MOJE_PLATBA   = GopayHelper::CZ_KB,
		EPLATBY       = GopayHelper::CZ_RB,
		MPENIZE       = GopayHelper::CZ_MB,
		BANK          = GopayHelper::CZ_BANK,
		PURSE         = GopayHelper::CZ_GP_W,
		MONEYBOOKERS  = GopayHelper::EU_MB_W,
		CARD_VISA     = GopayHelper::EU_MB_A,
		CARD_EXPRES   = GopayHelper::EU_MB_B;
	
	/** @var int */
	private $goId;
	
	/** @var string */
	private $secretKey;
	
	/** @var GopaySoap */
	private $soap;
	
	/** @var array */
	private $channels = array(
		self::SUPERCASH   => 'superCASH',
		self::MOJE_PLATBA => 'Mojeplatba',
		self::EPLATBY     => 'ePlatby',
		self::MPENIZE     => 'mPeníze',
		self::BANK        => 'Bankovní převod',
		self::PURSE       => 'GoPay peněženka',
	);
	
	public function __construct($values)
	{
		$this->soap = new GopaySoap;
		
		foreach ($this->getParameters() as $param) {
			if (isset($values[$param])) {
				$this->{'set' . ucfirst($param)}($values[$param]);
			}
		}
	}
	
	public static function create(array $values)
	{
		return new self($values);
	}
	
	private function getParameters()
	{
		return array('id', 'secretKey');
	}
	
	private function getIdentification()
	{
		return (object) array(
			'id'        => $this->goId,
			'secretKey' => $this->secretKey,
		);
	}
	
	public function setId($id)
	{
		$this->goId = (float) $id;
	}
	
	public function setSecretKey($secretKey)
	{
		$this->secretKey = $secretKey;
	}
	
/* === URL ================================================================== */
	
	/** @var string */
	private $success;
	
	public function getSuccess()
	{
		return $this->success;
	}
	
	public function setSuccess($success)
	{
		if (substr($success, 0, 7) !== 'http://') {
			$success = 'http:/' . $success;
		}
		
		$this->success = $success;
	}
	
	/** @var string */
	private $failure;
	
	public function getFailure()
	{
		return $this->failure;
	}
	
	public function setFailure($failure)
	{
		if (substr($failure, 0, 7) !== 'http://') {
			$failure = 'http:/' . $failure;
		}
		
		$this->failure = $failure;
	}
	
/* === Payments ============================================================= */
	
	/**
	 * Allows payment channel
	 * 
	 * @param  string
	 */
	public function allowChannel($channel)
	{
		$this->channels[$channel] = $this->knownChannels[$channel];
	}
	
	/**
	 * Denies payment channel
	 * 
	 * @param  string
	 */
	public function denyChannel($channel)
	{
		unset($this->channels[$channel]);
	}
	
	/**
	 * Creates new Payment with given default values
	 * 
	 * @param  array $values
	 * @return VojtechDobes\Gopay\Payment
	 */
	public function createPayment(array $values = array())
	{
		return new Payment($this, $this->getIdentification(), $values);
	}
	
	/**
	 * Executes payment via redirecting to GoPay payment gate
	 * 
	 * @param  VojtechDobes\Gopay\Payment $payment
	 */
	public function pay(Payment $payment, $channel)
	{
		error_reporting(E_ALL ^ E_NOTICE);
		
		if (!isset($this->channels[$channel])) {
			throw new InvalidArgumentException("Payment channel '$channel' is not supported");
		}
		
		$id = GopaySoap::createEshopPayment(
			$this->goId,
			$payment->getProduct(),
			$payment->getSum(),
			$payment->getVariable(),
			$this->success,
			$this->failure,
			$this->secretKey,
			array_keys($this->channels)
		);
		
		$payment->setId($id);
		
		$signature = $this->createSignature($id);
		
		$url = GopayHelper::fullIntegrationURL()
				. "?sessionInfo.eshopGoId=" . $this->goId
				. "&sessionInfo.paymentSessionId=" . $id
				. "&sessionInfo.encryptedSignature=" . $signature
				. "&paymentChannel=" . $channel;
		
		return new RedirectingResponse($url);
	}
	
	public function getReceivedPayment(array $values, array $valuesToBeVerified)
	{
		return new Payment($this, $this->getIdentification(), $values, $valuesToBeVerified);
	}
	
	/**
	 * Creates encrypted signature for given given payment session id
	 * 
	 * @param  int $paymentId
	 * @return string
	 */
	private function createSignature($paymentId)
	{
		return GopayHelper::encrypt(GopayHelper::hash(
			GopayHelper::concatPaymentSession(
				$this->goId,
				$paymentId,
				$this->secretKey
			)
		), $this->secretKey);
	}


}