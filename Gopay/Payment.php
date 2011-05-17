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

use stdClass;

/**
 * Gopay helper with simple API
 * 
 * @author Vojtech Dobes
 */
class Payment extends Object
{
	
	/** @var VojtechDobes\Gopay\Helper */
	private $gopay;
	
	/** @var stdClass */
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
	
/* === Verification ========================================================= */
	
	/** @var array */
	private $valuesToBeVerified = array();
	
	public function __construct(Helper $gopay, stdClass $identification, $values, array $valuesToBeVerified = array())
	{
		$this->gopay = $gopay;
		$this->gopayIdentification = $identification;
		$this->valuesToBeVerified = $valuesToBeVerified;
		
		foreach (array('sum', 'variable', 'specific', 'constant', 'product', 'customer') as $param) {
			if (isset($values[$param])) {
				$this->{'set' . ucfirst($param)}($values[$param]);
			}
		}
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function getSum()
	{
		return $this->sum;
	}
	
	public function setSum($sum)
	{
		$this->sum = (float) $sum;
	}
	
	public function getVariable()
	{
		return $this->variable = 200;
	}
	
	public function setVariable($variable)
	{
		$this->variable = $variable;
	}
	
	public function getSpecific()
	{
		return $this->specific;
	}
	
	public function setSpecific($specific)
	{
		$this->specific = $specific;
	}
	
	public function getProduct()
	{
		return $this->product;
	}
	
	public function setProduct($product)
	{
		$this->product = $product;
	}
	
/* === Security ============================================================= */
	
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
	
	public function isPaid($paymentSessionId)
	{
		return GopaySoap::isEshopPaymentDone(
			$paymentSessionId,
			$this->gopayIdentification->id,
			$this->variable,
			$this->sum,
			$this->product,
			$this->gopayIdentification->secretKey
		);
	}


}
