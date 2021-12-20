<?php declare(strict_types = 1);

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
	 */
	public function getSum(): ?float
	{
		return $this->sum;
	}

	/**
	 * Return sum in cents
	 */
	public function getSumInCents(): int
	{
		return (int) round($this->getSum() * 100);
	}

	/**
	 * Sets sum of payment
	 *
	 * @return static provides a fluent interface
	 */
	public function setSum(float $sum): self
	{
		$this->sum = (float) $sum;

		return $this;
	}

	/**
	 * Returns payment currency
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * Sets payment currency
	 *
	 * @throws InvalidArgumentException
	 */
	public function setCurrency(string $currency): self
	{
		if (!in_array($currency, $this->allowedCurrency)) {
			throw new InvalidArgumentException('Not supported currency "' . $currency . '".');
		}

		$this->currency = (string) $currency;

		return $this;
	}

	/**
	 * Returns variable symbol
	 */
	public function getVariable(): ?string
	{
		return $this->variable;
	}

	/**
	 * Sets variable symbol
	 */
	public function setVariable(string $variable): self
	{
		$this->variable = $variable;

		return $this;
	}

	/**
	 * Returns specific symbol
	 */
	public function getSpecific(): int
	{
		return $this->specific;
	}

	/**
	 * Sets specific symbol
	 */
	public function setSpecific(int $specific): self
	{
		$this->specific = (int) $specific;

		return $this;
	}

	/**
	 * Returns product name
	 */
	public function getProductName(): ?string
	{
		return $this->productName;
	}

	/**
	 * Sets product name
	 *
	 * @param string $name
	 * @return static
	 */
	public function setProductName(string $name): self
	{
		$this->productName = $name;

		return $this;
	}

	/**
	 * Returns customer data
	 */
	public function getCustomer(): stdClass
	{
		return $this->customer;
	}

	/**
	 * Sets customer data
	 *
	 * @param array|stdClass $customer
	 */
	public function setCustomer($customer): self
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
