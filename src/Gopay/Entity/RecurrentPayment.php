<?php declare(strict_types = 1);

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
	public const PERIOD_DAY = 'DAY';

	/** @const týdenní perioda plateb */
	public const PERIOD_WEEK = 'WEEK';

	/** @const měsíční perioda plateb */
	public const PERIOD_MOTNTH = 'MONTH';

	/** @var string */
	private $recurrenceDateTo = null;

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
	 */
	public function getRecurrenceCycle(): string
	{
		return $this->recurrenceCycle;
	}

	/**
	 * Sets cycle
	 *
	 * @param string $cycle DAY, MONTH, WEEK
	 */
	public function setRecurrenceCycle(string $cycle): void
	{
		if (!in_array($cycle, $this->allowedCycle)) {
			throw new InvalidArgumentException('Not supported cycle "' . $cycle . '".');
		}

		$this->recurrenceCycle = $cycle;
	}

	/**
	 * Return date to
	 */
	public function getRecurrenceDateTo()
	{
		return $this->recurrenceDateTo;
	}

	/**
	 * Sets expiration date
	 *
	 * @param string $date YYYY-MM-DD
	 */
	public function setRecurrenceDateTo(string $date): void
	{
		$this->recurrenceDateTo = $date;
	}

	/**
	 * Returns period
	 */
	public function getRecurrencePeriod(): int
	{
		return $this->recurrencePeriod;
	}

	/**
	 * Sets number of period
	 */
	public function setRecurrencePeriod(int $period): void
	{
		$this->recurrencePeriod = intval($period);
	}

}
