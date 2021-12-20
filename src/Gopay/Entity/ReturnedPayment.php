<?php declare(strict_types = 1);

namespace Markette\Gopay\Entity;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Exception\GopayFatalException;
use Markette\Gopay\Gopay;
use Throwable;

/**
 * Representation of payment returned from Gopay Payment Gate
 */
class ReturnedPayment extends Payment
{

	/** @var array */
	private $valuesToBeVerified = [];

	/** @var array */
	private $result;

	/** @var Gopay|null */
	private $gopay;

	/**
	 * @param array $values
	 * @param array $valuesToBeVerified
	 */
	public function __construct(array $values, array $valuesToBeVerified)
	{
		parent::__construct($values);
		$this->valuesToBeVerified = $valuesToBeVerified;
	}

	public function setGopay(Gopay $gopay): void
	{
		$this->gopay = $gopay;
	}

	/**
	 * @throws GopayException
	 */
	protected function getGopay(): Gopay
	{
		if (!$this->gopay) {
			throw new GopayException('No gopay set');
		}

		return $this->gopay;
	}

	/**
	 * Returns TRUE if payment is declared fraud by Gopay
	 *
	 * @throws GopayFatalException
	 */
	public function isFraud(): bool
	{
		try {
			$this->getGopay()->getHelper()->checkPaymentIdentity(
				(float) $this->valuesToBeVerified['targetGoId'],
				(float) $this->valuesToBeVerified['paymentSessionId'],
				0,
				$this->valuesToBeVerified['orderNumber'],
				$this->valuesToBeVerified['encryptedSignature'],
				(float) $this->getGopay()->getConfig()->getGopayId(),
				$this->getVariable(),
				$this->getGopay()->getConfig()->getGopaySecretKey()
			);

			return false;
		} catch (GopayFatalException $e) {
			throw $e;
		} catch (Throwable $e) {
			return true;
		}
	}

	/**
	 * Returns TRUE if payment is verified by Gopay as paid
	 */
	public function isPaid(): bool
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::PAID;
	}

	/**
	 * Returns TRUE if payment is waiting to be paid
	 */
	public function isWaiting(): bool
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::PAYMENT_METHOD_CHOSEN;
	}

	/**
	 * Returns TRUE if payment is canceled
	 */
	public function isCanceled(): bool
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::CANCELED;
	}

	/**
	 * Returns TRUE if payment is refunded
	 */
	public function isRefunded(): bool
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::REFUNDED;
	}

	/**
	 * Returns TRUE if payment is authorized
	 */
	public function isAuthorized(): bool
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::AUTHORIZED;
	}

	/**
	 * Returns TRUE if payment time limit already expired
	 *
	 * @return bool
	 */
	public function isTimeouted(): bool
	{
		$this->getStatus();

		return $this->result['sessionState'] === GopayHelper::TIMEOUTED;
	}

	/**
	 * Receives status of payment from Gopay WS
	 *
	 * @return array
	 */
	public function getStatus(): array
	{
		if ($this->result !== null) {
			return $this->result;
		}

		return $this->result = $this->getGopay()->getSoap()->isPaymentDone(
			(float) $this->valuesToBeVerified['paymentSessionId'],
			(float) $this->getGopay()->getConfig()->getGopayId(),
			$this->getVariable(),
			(int) $this->getSumInCents(),
			$this->getCurrency(),
			$this->getProductName(),
			$this->getGopay()->getConfig()->getGopaySecretKey()
		);
	}

}
