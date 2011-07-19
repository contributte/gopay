<?php

/**
 * Gopay Wrapper
 * 
 * @author Vojtech Dobes
 */

namespace Gopay;

use GopayHelper;
use GopaySoap;

use Nette\Object;

/**
 * Representation of payment
 * 
 * @author   Vojtech Dobes
 * @package  Gopay Wrapper
 * @property $id
 * @property $sum
 * @property $variable
 * @property $specific
 * @property $product
 * @property $customer
 */
class Payment extends Object
{
	
	/** @var \Gopay\Helper */
	protected $gopay;
	
	/** @var \stdClass */
	protected $gopayIdentification;
	
/* === Description ========================================================== */	
	
	/** @var int */
	protected $sum;
	
	/** @var int */
	protected $variable;
	
	/** @var int */
	protected $specific;
	
	/** @var string */
	protected $product;

	/** @var \stdClass */
	protected $customer;

	/**
	 * @param  \Gopay\Helper $gopay
	 * @param  \stdClass $identification
	 * @param  array $values
	 */
	public function __construct(Helper $gopay, \stdClass $identification, $values)
	{
		$this->gopay = $gopay;
		$this->gopayIdentification = $identification;
		
		foreach (array('sum', 'variable', 'specific', 'constant', 'product', 'customer') as $param) {
			if (isset($values[$param])) {
				$this->{'set' . ucfirst($param)}($values[$param]);
			}
		}
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

	public function getCustomer()
	{
		return $this->customer;
	}

	public function setCustomer($customer)
	{
		$this->customer = (object) $customer;

		foreach (array() as $key) {
			if (!isset($this->customer->$key)) {
				$this->customer->$key = '';
			}
		}
	}


}
