<?php

namespace Markette\Gopay\Service;

use Markette\Gopay\Exception\InvalidArgumentException;
use Markette\Gopay\Gopay;

/**
 * Abstract Service
 */
abstract class AbstractService
{

	/** @var bool */
	protected $changeChannel;

	/** @var string */
	protected $lang = Gopay::LANG_CS;

	/** @var string */
	protected $successUrl;

	/** @var string */
	protected $failureUrl;

	/** @var array */
	protected $channels = [];

	/** @var array */
	protected $allowedLang = [
		Gopay::LANG_CS,
		Gopay::LANG_EN,
		Gopay::LANG_SK,
		Gopay::LANG_DE,
		Gopay::LANG_RU,
	];

	/**
	 * @param bool $changeChannel
	 * @return static
	 */
	public function allowChangeChannel($changeChannel = TRUE)
	{
		$this->changeChannel = (bool) $changeChannel;

		return $this;
	}

	/**
	 * Sets payment gateway language
	 *
	 * @param string $lang
	 * @throws InvalidArgumentException if language is not supported
	 * @return static
	 */
	public function setLang($lang)
	{
		if (!in_array($lang, $this->allowedLang)) {
			throw new InvalidArgumentException('Not supported language "' . $lang . '".');
		}
		$this->lang = $lang;

		return $this;
	}

	/**
	 * Returns success URL
	 *
	 * @return string
	 */
	public function getSuccessUrl()
	{
		return $this->successUrl;
	}

	/**
	 * Sets URL when successful
	 *
	 * @param string $url
	 * @return static
	 */
	public function setSuccessUrl($url)
	{
		if (substr($url, 0, 4) !== 'http') {
			$url = 'http://' . $url;
		}

		$this->successUrl = $url;

		return $this;
	}

	/**
	 * Returns failed URL
	 *
	 * @return string
	 */
	public function getFailureUrl()
	{
		return $this->failureUrl;
	}

	/**
	 * Sets URL when failed
	 *
	 * @param string $url
	 * @return static
	 */
	public function setFailureUrl($url)
	{
		if (substr($url, 0, 4) !== 'http') {
			$url = 'http://' . $url;
		}

		$this->failureUrl = $url;

		return $this;
	}

	/**
	 * Adds custom payment channel
	 *
	 * @param string $code
	 * @param string $name
	 * @param string $logo
	 * @param string $offline
	 * @param string $description
	 * @param array $params
	 * @throws InvalidArgumentException on channel name conflict
	 * @return static
	 */
	public function addChannel($code, $name, $logo = NULL, $offline = NULL, $description = NULL, array $params = [])
	{
		if (isset($this->channels[$code])) {
			throw new InvalidArgumentException(sprintf('Channel with name \'%s\' is already defined.', $code));
		}

		$this->channels[$code] = (object) array_merge($params, [
			'code' => $code,
			'name' => $name,
			'logo' => $logo,
			'offline' => $offline,
			'description' => $description,
		]);

		return $this;
	}

	/**
	 * Returns list of payment channels
	 *
	 * @return array
	 */
	public function getChannels()
	{
		return $this->channels;
	}

}
