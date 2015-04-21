<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;

use Nette;
use stdClass;



/**
 * Representation of payment
 *
 * @author Vojtěch Dobeš
 * @author Jan Skrasek
 *
 * @property       $sum
 * @property-read  $sumInCents
 * @property       $currency
 * @property       $variable
 * @property       $specific
 * @property       $productName
 * @property       $customer
 */
class Payment extends Nette\Object
{

	/** @var float */
	private $sum;

	/** @var string */
	private $currency = Service::CURRENCY_CZK;

	/** @var int */
	private $variable;

	/** @var int */
	private $specific;

	/** @var string */
	private $productName;

	/** @var stdClass */
	private $customer;

	/** @var array */
	private $allowedCurrency = array(
		Service::CURRENCY_CZK,
		Service::CURRENCY_EUR,
	);



	/**
	 * @param  Service
	 * @param  stdClass
	 * @param  array|stdClass
	 */
	public function __construct(array $values)
	{
		foreach (array('sum', 'currency', 'variable', 'specific', 'productName', 'customer') as $param) {
			if (isset($values[$param])) {
				$this->{'set' . ucfirst($param)}($values[$param]);
			}
		}
	}



	/**
	 * Returns sum of payment
	 * @return float
	 */
	public function getSum()
	{
		return $this->sum;
	}



	/**
	 * Return sum in cents
	 * @return int
	 */
	public function getSumInCents()
	{
		return round($this->getSum() * 100);
	}



	/**
	 * Sets sum of payment
	 * @param  float
	 * @return static provides a fluent interface
	 */
	public function setSum($sum)
	{
		$this->sum = (float) $sum;
		return $this;
	}



	/**
	 * Returns payment currency
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}



	/**
	 * Sets payment currency
	 * @param  string
	 * @return static provides a fluent interface
	 */
	public function setCurrency($currency)
	{
		if (!in_array($currency, $this->allowedCurrency)) {
			throw new \InvalidArgumentException('Not supported currency "' . $currency . '".');
		}
		$this->currency = (string) $currency;
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
	 * @return static provides a fluent interface
	 */
	public function setVariable($variable)
	{
		$this->variable = (int) $variable;
		return $this;
	}



	/**
	 * Returns specific symbol
	 *
	 * @return int
	 */
	public function getSpecific()
	{
		return $this->specific;
	}



	/**
	 * Sets specific symbol
	 *
	 * @param  int
	 * @return static provides a fluent interface
	 */
	public function setSpecific($specific)
	{
		$this->specific = (int) $specific;
		return $this;
	}



	/**
	 * Returns product name
	 *
	 * @return string
	 */
	public function getProductName()
	{
		return $this->productName;
	}



	/**
	 * Sets product name
	 *
	 * @param $name
	 * @return static provides a fluent interface
	 */
	public function setProductName($name)
	{
		$this->productName = $name;
		return $this;
	}



	/**
	 * Returns customer data
	 *
	 * @return stdClass
	 */
	public function getCustomer()
	{
		return $this->customer;
	}



	/**
	 * Sets customer data
	 *
	 * @param  array|stdClass
	 * @return static provides a fluent interface
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
