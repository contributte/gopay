<?php

namespace Markette\Gopay\Entity;

use Markette\Gopay\Exception\InvalidArgumentException;
use Markette\Gopay\Gopay;
use stdClass;

/**
 * Representation of base payment
 *
 * @property       float $sum
 * @property-read  int $sumInCents
 * @property       string $currency
 * @property       int $variable
 * @property       int $specific
 * @property       string $productName
 * @property       stdClass $customer
 */
abstract class BasePayment
{

	/** @var float */
	private $sum;

	/** @var string */
	private $currency = Gopay::CURRENCY_CZK;

	/** @var string */
	private $variable;

	/** @var int */
	private $specific;

	/** @var string */
	private $productName;

	/** @var stdClass */
	private $customer;

	/** @var array */
	private $allowedCurrency = [
		Gopay::CURRENCY_CZK,
		Gopay::CURRENCY_EUR,
		Gopay::CURRENCY_PLN,
		Gopay::CURRENCY_HUF,
		Gopay::CURRENCY_GBP,
		Gopay::CURRENCY_USD,
	];

	/**
	 * @param array $values
	 */
	public function __construct(array $values)
	{
		foreach (['sum', 'currency', 'variable', 'specific', 'productName', 'customer'] as $param) {
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
	 * Return sum in cents
	 *
	 * @return int
	 */
	public function getSumInCents()
	{
		return round($this->getSum() * 100);
	}

	/**
	 * Sets sum of payment
	 *
	 * @param float $sum
	 * @return static provides a fluent interface
	 */
	public function setSum($sum)
	{
		$this->sum = (float) $sum;

		return $this;
	}

	/**
	 * Returns payment currency
	 *
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * Sets payment currency
	 *
	 * @param string $currency
	 * @throws InvalidArgumentException
	 * @return static
	 */
	public function setCurrency($currency)
	{
		if (!in_array($currency, $this->allowedCurrency)) {
			throw new InvalidArgumentException('Not supported currency "' . $currency . '".');
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
	 * @param string $variable
	 * @return static
	 */
	public function setVariable($variable)
	{
		$this->variable = $variable;

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
	 * @param int $specific
	 * @return static
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
	 * @param string $name
	 * @return static
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
	 * @param array|stdClass $customer
	 * @return static
	 */
	public function setCustomer($customer)
	{
		$allowedKeys = [
			'firstName',
			'lastName',
			'street',
			'city',
			'postalCode',
			'countryCode',
			'email',
			'phoneNumber',
		];

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
