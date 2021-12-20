<?php declare(strict_types = 1);

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

	public function allowChangeChannel(?bool $changeChannel = true): self
	{
		$this->changeChannel = (bool) $changeChannel;

		return $this;
	}

	/**
	 * Sets payment gateway language
	 *
	 * @throws InvalidArgumentException if language is not supported
	 */
	public function setLang(string $lang): self
	{
		if (!in_array($lang, $this->allowedLang)) {
			throw new InvalidArgumentException('Not supported language "' . $lang . '".');
		}

		$this->lang = $lang;

		return $this;
	}

	/**
	 * Returns success URL
	 */
	public function getSuccessUrl(): string
	{
		return $this->successUrl;
	}

	/**
	 * Sets URL when successful
	 */
	public function setSuccessUrl(string $url): self
	{
		if (substr($url, 0, 4) !== 'http') {
			$url = 'http://' . $url;
		}

		$this->successUrl = $url;

		return $this;
	}

	/**
	 * Returns failed URL
	 */
	public function getFailureUrl(): string
	{
		return $this->failureUrl;
	}

	/**
	 * Sets URL when failed
	 */
	public function setFailureUrl(string $url): self
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
	 */
	public function addChannel(
		string $code,
		string $name,
		?string $logo = null,
		?string $offline = null,
		?string $description = null,
		array $params = []
	): self
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
	 */
	public function getChannels(): array
	{
		return $this->channels;
	}

}
