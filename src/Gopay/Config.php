<?php

namespace Markette\Gopay;

use Markette\Gopay\Api\GopayConfig;

class Config
{

	/** @var float */
	private $gopayId;

	/** @var string */
	private $gopaySecretKey;

	/** @var bool */
	private $testMode = FALSE;

	/**
	 * @param float $gopayId
	 * @param string $gopaySecretKey
	 * @param bool $testMode
	 */
	public function __construct($gopayId, $gopaySecretKey, $testMode)
	{
		$this->setGopayId($gopayId);
		$this->setGopaySecretKey($gopaySecretKey);
		$this->setTestMode($testMode);
	}

	/**
	 * @return float
	 */
	public function getGopayId()
	{
		return $this->gopayId;
	}

	/**
	 * @param float $id
	 * @return void
	 */
	protected function setGopayId($id)
	{
		$this->gopayId = (float) $id;
	}


	/**
	 * @return string
	 */
	public function getGopaySecretKey()
	{
		return $this->gopaySecretKey;
	}

	/**
	 * @param string $secretKey
	 * @return void
	 */
	protected function setGopaySecretKey($secretKey)
	{
		$this->gopaySecretKey = (string) $secretKey;
	}

	/**
	 * @return boolean
	 */
	public function isTestMode()
	{
		return $this->testMode;
	}

	/**
	 * @param bool $testMode
	 * @return void
	 */
	protected function setTestMode($testMode = TRUE)
	{
		$this->testMode = (bool) $testMode;
		GopayConfig::$version = $this->testMode ? GopayConfig::TEST : GopayConfig::PROD;
	}

}
