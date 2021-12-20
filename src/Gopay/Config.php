<?php declare(strict_types = 1);

namespace Markette\Gopay;

use Markette\Gopay\Api\GopayConfig;

class Config
{

	/** @var float */
	private $gopayId;

	/** @var string */
	private $gopaySecretKey;

	/** @var bool */
	private $testMode = false;

	public function __construct($gopayId, $gopaySecretKey, $testMode)
	{
		$this->setGopayId($gopayId);
		$this->setGopaySecretKey($gopaySecretKey);
		$this->setTestMode($testMode);
	}

	public function getGopayId(): float
	{
		return $this->gopayId;
	}

	protected function setGopayId(?float $id): void
	{
		$this->gopayId = (float) $id;
	}


	public function getGopaySecretKey(): string
	{
		return $this->gopaySecretKey;
	}

	protected function setGopaySecretKey($secretKey): void
	{
		$this->gopaySecretKey = (string) $secretKey;
	}

	public function isTestMode(): bool
	{
		return $this->testMode;
	}

	protected function setTestMode($testMode = true): void
	{
		$this->testMode = (bool) $testMode;
		GopayConfig::$version = $this->testMode ? GopayConfig::TEST : GopayConfig::PROD;
	}

}
