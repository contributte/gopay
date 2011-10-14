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
 * @property $sum
 * @property $variable
 * @property $specific
 * @property $customer
 */
class Payment extends Object
{

	/** @var \Gopay\Service */
	protected $gopay;

	/** @var \stdClass */
	protected $gopayIdentification;

/* === Description ========================================================== */	

	/** @var int */
	private $sum;

	/** @var int */
	private $variable;

	/** @var int */
	private $specific;

	/** @var string */
	public $product;

	/** @var \stdClass */
	private $customer;


	/**
	 * @param  \Gopay\Service
	 * @param  \stdClass
	 * @param  array|\stdClass
	 */
	public function __construct(Service $gopay, \stdClass $identification, $values)
	{
		$this->gopay = $gopay;
		$this->gopayIdentification = $identification;
		
		$values = (array) $values;
		foreach (array('sum', 'variable', 'specific', 'constant', 'product', 'customer') as $param) {
			if (isset($values[$param])) {
				$this->{'set' . ucfirst($param)}($values[$param]);
			}
		}
	}


	/**
	 * Returns sum of payment
	 *
	 * @return float
	 */
	public function getSum()
	{
		return $this->sum;
	}


	/**
	 * Sets sum of payment
	 *
	 * @param  float
	 * @return provides a fluent interface
	 */
	public function setSum($sum)
	{
		$this->sum = (float) $sum;
		return $this;
	}


	/**
	 * Returns variable symbol
	 *
	 * @return int
	 */
	public function getVariable()
	{
		return $this->variable;
	}


	/**
	 * Sets variable symbol
	 *
	 * @param  int
	 * @return provides a fluent interface 
	 */
	public function setVariable($variable)
	{
		$this->variable = (int) $variable;
		return $this;
	}


	public function getSpecific()
	{
		return $this->specific;
	}


	/**
	 * Sets specific symbol
	 *
	 * @param  int
	 * @return provides a fluent interface
	 */
	public function setSpecific($specific)
	{
		$this->specific = (int) $specific;
		return $this;
	}


	/**
	 * Returns customer data
	 *
	 * @return \stdClass
	 */
	public function getCustomer()
	{
		return $this->customer;
	}


	/**
	 * Sets customer data
	 *
	 * @param  array|\stdClass
	 * @return provides a fluent interface
	 */
	public function setCustomer($customer)
	{
		$allowedKeys = array(
			'firstName',
			'lastName',
			'street',
			'city',
			'postalCode',
			'countryCode',
			'email',
			'phoneNumber',
		);
		$this->customer = (object) array_intersect_key(
			(array) $customer,
			array_flip($allowedKeys)
		);

		foreach ($allowedKeys as $key) {
			if (!isset($this->customer->$key)) {
				$this->customer->$key = '';
			}
		}
		return $this;
	}

}
