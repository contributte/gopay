<?php

namespace Markette\Gopay\Entity;

use Markette\Gopay\Exception\InvalidArgumentException;

/**
 * Representation of recurrent payment
 *
 * @property string $recurrenceDateTo
 * @property string $recurrenceCycle
 * @property int $recurrencePeriod
 */
class RecurrentPayment extends BasePayment
{

	/** @const denní perioda plateb */
	const PERIOD_DAY = 'DAY';

	/** @const týdenní perioda plateb */
	const PERIOD_WEEK = 'WEEK';

	/** @const měsíční perioda plateb */
	const PERIOD_MOTNTH = 'MONTH';

	/** @var string */
	private $recurrenceDateTo = NULL;

	/** @var string */
	private $recurrenceCycle = self::PERIOD_DAY;

	/** @var array */
	private $allowedCycle = [
		self::PERIOD_DAY,
		self::PERIOD_WEEK,
		self::PERIOD_MOTNTH,
	];

	/** @var int */
	private $recurrencePeriod = 30;

	/**
	 * @param array $values
	 */
	public function __construct(array $values)
	{
		parent::__construct($values);
		foreach (['recurrenceCycle', 'recurrenceDateTo', 'recurrencePeriod'] as $param) {
			if (isset($values[$param])) {
				$this->{'set' . ucfirst($param)}($values[$param]);
			}
		}
	}

	/**
	 * Returns cycle
	 *
	 * @return string
	 */
	public function getRecurrenceCycle()
	{
		return $this->recurrenceCycle;
	}

	/**
	 * Sets cycle
	 *
	 * @param string $cycle DAY, MONTH, WEEK
	 * @return void
	 */
	public function setRecurrenceCycle($cycle)
	{
		if (!in_array($cycle, $this->allowedCycle)) {
			throw new InvalidArgumentException('Not supported cycle "' . $cycle . '".');
		}

		$this->recurrenceCycle = $cycle;
	}

	/**
	 * Return date to
	 *
	 * @return string
	 */
	public function getRecurrenceDateTo()
	{
		return $this->recurrenceDateTo;
	}

	/**
	 * Sets expiration date
	 *
	 * @param string $date YYYY-MM-DD
	 * @return void
	 */
	public function setRecurrenceDateTo($date)
	{
		$this->recurrenceDateTo = $date;
	}

	/**
	 * Returns period
	 *
	 * @return int
	 */
	public function getRecurrencePeriod()
	{
		return $this->recurrencePeriod;
	}

	/**
	 * Sets number of period
	 *
	 * @param int $period
	 * @return void
	 */
	public function setRecurrencePeriod($period)
	{
		$this->recurrencePeriod = intval($period);
	}

}
