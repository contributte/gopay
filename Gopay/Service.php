<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Markette\Gopay\Api\GopayConfig;
use Markette\Gopay\Api\PaymentMethodElement;
use Nette;
use Nette\Application\Responses\RedirectResponse;
use Nette\DI\Container;



/**
 * Gopay wrapper with simple API
 *
 * @author Vojtěch Dobeš
 * @author Jan Skrasek
 *
 * @property-write $gopayId
 * @property-write $gopaySecretKey
 * @property-write $testMode
 */
class Service extends Nette\Object
{

	/** @const Česká spořitelna, a.s. E-commerce 3-D Secure */
	const METHOD_CARD_CESKAS = 'cz_cs_c';
	/** @const UniCredit Bank - Global payments */
	const METHOD_CARD_UNICREDITB = 'eu_gp_u';

	/** @const Terminál České pošty, Sazka a.s. */
	const METHOD_SUPERCASH = 'SUPERCASH';
	/** @const Mobilní telefon - Premium SMS */
	const METHOD_PREMIUMSMS = 'eu_pr_sms';
	/** @const Mobilní telefon - platební brána operátora */
	const METHOD_MPLATBA = 'cz_mp';

	/** @const Platební tlačítko - Internetové bankovnictví Komerční banky a.s. */
	const METHOD_KOMERCNIB = 'cz_kb';
	/** @const Platební tlačítko - Internetové bankovnictví	Raiffeisenbank a.s. */
	const METHOD_RAIFFEISENB = 'cz_rb';
	/** @const Platební tlačítko - Internetové bankovnictví	MBank */
	const METHOD_MBANK = 'cz_mb';
	/** @const Platební tlačítko - Internetové bankovnictví	Fio banky */
	const METHOD_FIOB = 'cz_fb';
	/** @const Platební tlačítko - Internetové bankovnictví	UniCredit Bank a.s. */
	const METHOD_UNICREDITB = 'sk_uni';
	/** @const Platební tlačítko - Internetové bankovnictví	Slovenská sporiteľňa, a. s. */
	const METHOD_SLOVENSKAS = 'sk_sp';

	/** @const Běžný bankovní převod */
	const METHOD_TRANSFER = 'eu_bank';
	/** @const Gopay - Elektronická peněženka. */
	const METHOD_GOPAY = 'eu_gp_w';


	/** @const Czech koruna */
	const CURRENCY_CZK = 'CZK';
	/** @const Euro */
	const CURRENCY_EUR = 'EUR';


	/** @const Czech */
	const LANG_CS = 'CS';
	/** @const English */
	const LANG_EN = 'EN';


	/** @var GopaySoap */
	private $soap;

	/** @var float */
	private $gopayId;

	/** @var string */
	private $gopaySecretKey;

	/** @var bool */
	private $testMode = FALSE;

	/** @var string */
	private $lang = self::LANG_CS;

	/** @var string */
	private $successUrl;

	/** @var string */
	private $failureUrl;

	/** @var array */
	private $allowedChannels = NULL;

	/** @var array */
	private $deniedChannels = NULL;

	/** @var array */
	private $allowedLang = array(
		self::LANG_CS,
		self::LANG_EN,
	);



	/**
	 * @param GopaySoap
	 * @param float
	 * @param string
	 * @param bool
	 */
	public function __construct(GopaySoap $soap, $gopayId, $gopaySecretKey, $testMode)
	{
		$this->soap = $soap;
		$this->setGopayId($gopayId);
		$this->setGopaySecretKey($gopaySecretKey);
		$this->setTestMode($testMode);
	}



	/**
	 * Sets Gopay ID number
	 *
	 * @param  float
	 * @return static provides a fluent interface
	 */
	public function setGopayId($id)
	{
		$this->gopayId = (float) $id;
		return $this;
	}



	/**
	 * Sets Gopay secret key
	 *
	 * @param  string
	 * @return static provides a fluent interface
	 */
	public function setGopaySecretKey($secretKey)
	{
		$this->gopaySecretKey = (string) $secretKey;
		return $this;
	}



	/**
	 * Sets state of test mode
	 *
	 * @param  bool
	 * @return static provides a fluent interface
	 */
	public function setTestMode($testMode = TRUE)
	{
		$this->testMode = (bool) $testMode;
		GopayConfig::$version = $this->testMode ? GopayConfig::TEST : GopayConfig::PROD;
		return $this;
	}



	/**
	 * Sets payment gateway language
	 * @param  string
	 * @throws \InvalidArgumentException if language is not supported
	 * @return static provides a fluent interface
	 */
	public function setLang($lang)
	{
		if (!in_array($lang, $this->allowedLang)) {
			throw new \InvalidArgumentException('Not supported language "' . $lang . '".');
		}
		$this->lang = $lang;
		return $this;
	}


	/**
	 * Returns URL when successful
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
	 * @param  string
	 * @return static provides a fluent interface
	 */
	public function setSuccessUrl($absoluteUrl)
	{
		if (substr($absoluteUrl, 0, 4) !== 'http') {
			$absoluteUrl = 'http://' . $absoluteUrl;
		}

		$this->successUrl = $absoluteUrl;
		return $this;
	}



	/**
	 * Returns URL when failed
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
	 * @param  string
	 * @return static provides a fluent interface
	 */
	public function setFailureUrl($absoluteUrl)
	{
		if (substr($absoluteUrl, 0, 4) !== 'http') {
			$absoluteUrl = 'http://' . $absoluteUrl;
		}

		$this->failureUrl = $absoluteUrl;
		return $this;
	}



	/**
	 * Allows payment channel
	 *
	 * @param  string
	 * @return static provides a fluent interface
	 * @throws \InvalidArgumentException on undefined or already allowed channel
	 */
	public function allowChannel($channel)
	{
		$this->getChannels();

		if (!isset($this->deniedChannels[$channel])) {
			throw new \InvalidArgumentException("Channel with name '$channel' isn't defined.");
		}

		$this->allowedChannels[$channel] = $this->deniedChannels[$channel];
		unset($this->deniedChannels[$channel]);

		return $this;
	}



	/**
	 * Denies payment channel
	 *
	 * @param  string
	 * @return static provides a fluent interface
	 * @throws \InvalidArgumentException on undefined or already denied channel
	 */
	public function denyChannel($channel)
	{
		$this->getChannels();

		if (!isset($this->allowedChannels[$channel])) {
			throw new \InvalidArgumentException("Channel with name '$channel' isn't defined.");
		}

		$this->deniedChannels[$channel] = $this->allowedChannels[$channel];
		unset($this->allowedChannels[$channel]);

		return $this;
	}



	/**
	 * Adds custom payment channel
	 *
	 * @param  string
	 * @param  string
	 * @param  string|NULL
	 * @return static provides a fluent interface
	 * @throws \InvalidArgumentException on channel name conflict
	 */
	public function addChannel($channel, $title, array $params = array())
	{
		$this->getChannels();

		if (isset($this->allowedChannels[$channel]) || isset($this->deniedChannels[$channel])) {
			throw new \InvalidArgumentException("Channel with name '$channel' is already defined.");
		}

		$this->allowedChannels[$channel] = (object) array_merge($params, array(
			'title' => $title,
		));

		return $this;
	}



	/**
	 * Adds payment channel received from Gopay WS
	 *
	 * @param  PaymentMethodElement
	 * @return static provides a fluent interface
	 * @throws \InvalidArgumentException on channel name conflict
	 */
	public function addRawChannel(PaymentMethodElement $element)
	{
		return $this->addChannel($element->code, $element->paymentMethodName, array(
			'image' => $element->logo,
			'offline' => $element->offline,
			'description' => $element->description,
		));
	}



	/**
	 * Returns list of allowed payment channels
	 *
	 * @return array
	 */
	public function getChannels()
	{
		if ($this->allowedChannels === NULL || $this->deniedChannels === NULL) {
			$this->loadGopayChannels();
		}

		return $this->allowedChannels;
	}



	/**
	 * Creates new Payment with given default values
	 *
	 * @param  array
	 * @return Payment
	 */
	public function createPayment(array $values = array())
	{
		return new Payment($values);
	}



	/**
	 * Returns payment after visiting Payment Gate
	 *
	 * @param  array
	 * @param  array
	 * @return ReturnedPayment
	 */
	public function restorePayment($values, $valuesToBeVerified)
	{
		return new ReturnedPayment($values, $this->gopayId, $this->gopaySecretKey, (array) $valuesToBeVerified);
	}



	/**
	 * Executes payment via redirecting to GoPay payment gate
	 *
	 * @param  Payment
	 * @param  string
	 * @param  callback
	 * @return RedirectResponse
	 * @throws \InvalidArgumentException on undefined channel or provided ReturnedPayment
	 * @throws GopayFatalException on maldefined parameters
	 * @throws GopayException on failed communication with WS
	 */
	public function pay(Payment $payment, $channel, $callback = NULL)
	{
		if ($payment instanceof ReturnedPayment) {
			throw new \InvalidArgumentException("Cannot use instance of 'ReturnedPayment'! This payment has been already used for paying");
		}

		if (!isset($this->allowedChannels[$channel])) {
			throw new \InvalidArgumentException("Payment channel '$channel' is not supported");
		}

		try {
			$customer = $payment->getCustomer();
			$paymentSessionId = GopaySoap::createPayment(
				$this->gopayId,
				$payment->getProductName(),
				$payment->getSumInCents(),
				$payment->getCurrency(),
				$payment->getVariable(),
				$this->successUrl,
				$this->failureUrl,
				array_keys($this->allowedChannels),
				$channel,
				$this->gopaySecretKey,
				$customer->firstName,
				$customer->lastName,
				$customer->city,
				$customer->street,
				$customer->postalCode,
				$customer->countryCode,
				$customer->email,
				$customer->phoneNumber,
				NULL, NULL, NULL, NULL,
				$this->lang
			);
		} catch(\Exception $e) {
			throw new GopayException($e->getMessage(), 0, $e);
		}

		$url = GopayConfig::fullIntegrationURL()
			. "?sessionInfo.targetGoId=" . $this->gopayId
			. "&sessionInfo.paymentSessionId=" . $paymentSessionId
			. "&sessionInfo.encryptedSignature=" . $this->createSignature($paymentSessionId);

		$callback = new Nette\Callback($callback);
		if ($callback->isCallable()) {
			$callback->invokeArgs(array($paymentSessionId));
		}

		return new RedirectResponse($url);
	}



	/**
	 * Binds form to Gopay
	 * - adds payment buttons
	 *
	 * @param  Form
	 * @param  array|callable
	 */
	public function bindPaymentButtons(Nette\Forms\Container $form, $callbacks)
	{
		foreach ($this->allowedChannels as $name => $channel) {
			if (!isset($channel->image)) {
				$button = $form['gopayChannel' . $name] = new PaymentButton($name, $channel->title);
			} else {
				$button = $form['gopayChannel' . $name] = new ImagePaymentButton($name, $channel->image, $channel->title);
			}

			if (!is_array($callbacks)) $callbacks = array($callbacks);
			foreach ($callbacks as $callback) {
				$button->onClick[] = $callback;
			}

			$this->allowedChannels[$name]->control = 'gopayChannel' . $name;
		}
	}



	/**
	 * Setups default set of payment channels
	 *
	 * @throws GopayException on failed communication with WS
	 */
	private function loadGopayChannels()
	{
		$this->allowedChannels = $this->deniedChannels = array();
		$methodList = GopaySoap::paymentMethodList();
		if ($methodList === NULL) {
			throw new GopayFatalException('Loading of native Gopay payment channels failed due to communication with WS.');
		}
		foreach ($methodList as $method) {
			$this->addRawChannel($method);
		}
	}



	/**
	 * Creates encrypted signature for given given payment session id
	 *
	 * @param  int
	 * @return string
	 */
	private function createSignature($paymentSessionId)
	{
		return GopayHelper::encrypt(
			GopayHelper::hash(
				GopayHelper::concatPaymentSession(
					(float) $this->gopayId,
					(float) $paymentSessionId,
					$this->gopaySecretKey
				)
			),
			$this->gopaySecretKey
		);
	}

}
