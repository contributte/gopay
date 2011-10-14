<?php

/**
 * Gopay Wrapper
 * 
 * @author Vojtech Dobes
 */

namespace Gopay;

use GopayHelper;
use GopaySoap;

use Nette\Object;
use Nette\Application\Responses\RedirectResponse;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;


/**
 * Gopay wrapper with simple API
 *
 * @author         Vojtech Dobes
 * @dependency     mcrypt
 * @package        Gopay Wrapper
 * @property-read  $channels
 * @property-write $id
 * @property-write $secretKey
 * @property-write $imagePath
 * @property-write $testMode
 * @property-write $success
 * @property-write $failure
 */
class Helper extends Object
{
	
	/** @const string */
	const SUPERCASH = GopayHelper::SUPERCASH,
		MOJE_PLATBA   = GopayHelper::CZ_KB,
		EPLATBY       = GopayHelper::CZ_RB,
		MPENIZE       = GopayHelper::CZ_MB,
		BANK          = GopayHelper::CZ_BANK,
		PURSE         = GopayHelper::CZ_GP_W,
		MONEYBOOKERS  = GopayHelper::EU_MB_W,
		CARD_VISA     = GopayHelper::EU_MB_A,
		CARD_EXPRES   = GopayHelper::EU_MB_B;
	
	/** @var int */
	private $goId;
	
	/** @var string */
	private $secretKey;
	
	/** @var string */
	private $imagePath;
	
	/** @var bool */
	private $testMode = FALSE;
	
	/** @var \GopaySoap */
	private $soap;
	
	/** @var array */
	private $channels = array(
		self::SUPERCASH    => 'superCASH',
		self::MOJE_PLATBA  => 'Mojeplatba',
		self::EPLATBY      => 'ePlatby',
		self::MPENIZE      => 'mPeníze',
		self::BANK         => 'Bankovní převod',
		self::PURSE        => 'GoPay peněženka',
		self::MONEYBOOKERS => 'Moneybookers peněženka',
		self::CARD_VISA    => 'Platební karty MasterCard, Maestro a Visa',
		self::CARD_EXPRES  => 'Platební karty American Expres a JCB',
	);

	/**
	 * Accepts initial directives (possibly from config)
	 *
	 * @param  array $values
	 */
	public function __construct($values)
	{
		$this->soap = new GopaySoap;
		
		foreach (array('id', 'secretKey', 'imagePath', 'testMode') as $param) {
			if (isset($values[$param])) {
				$this->{'set' . ucfirst($param)}($values[$param]);
			}
		}
		
		GopayHelper::$testMode = $this->testMode;
		
		$this->setupChannels();
	}

	/**
	 * Static factory
	 *
	 * @static
	 * @param  array $values
	 * @return \Gopay\Helper
	 */
	public static function create(array $values)
	{
		return new self($values);
	}

	/**
	 * Returns simple envelope with identification of eshop
	 *
	 * @return \stdClass
	 */
	private function getIdentification()
	{
		return (object) array(
			'id'        => $this->goId,
			'secretKey' => $this->secretKey,
		);
	}
	
	public function setId($id)
	{
		$this->goId = (float) $id;
	}
	
	public function setSecretKey($secretKey)
	{
		$this->secretKey = $secretKey;
	}
	
	public function setImagePath($imagePath)
	{
		$this->imagePath = $imagePath;
	}
	
	public function setTestMode($testMode = TRUE)
	{
		$this->testMode = $testMode;
	}
	
/* === URL ================================================================== */
	
	/** @var string */
	private $success;
	
	public function getSuccess()
	{
		return $this->success;
	}
	
	public function setSuccess($success)
	{
		if (substr($success, 0, 7) !== 'http://') {
			$success = 'http:/' . $success;
		}
		
		$this->success = $success;
	}
	
	/** @var string */
	private $failure;
	
	public function getFailure()
	{
		return $this->failure;
	}
	
	public function setFailure($failure)
	{
		if (substr($failure, 0, 7) !== 'http://') {
			$failure = 'http:/' . $failure;
		}

		$this->failure = $failure;
	}

/* === Payment Channels ===================================================== */

	/** @var array */
	private $allowedChannels = array();

	/** @var array */
	private $deniedChannels = array();


	/**
	 * Allows payment channel
	 * 
	 * @param  string
	 * @return provides a fluent interface
	 * @throws \Nette\InvalidArgumentException on undefined or already allowed channel
	 */
	public function allowChannel($channel)
	{
		if (isset($this->allowedChannels[$channel])) {
			throw InvalidArgumentException("Channel with name '$channel' is already allowed.");
		} else if (!isset($this->deniedChannels[$channel])) {
			throw InvalidArgumentException("Channel with name '$channel' isn't defined.");
		}

		$this->allowedChannels[$channel] = $this->deniedChannels[$channel];
		unset($this->deniedChannels[$channel]);

		return $this;
	}


	/**
	 * Denies payment channel
	 * 
	 * @param  string
	 * @return provides a fluent interface
	 * @throws \Nette\InvalidArgumentException on undefined or already denied channel
	 */
	public function denyChannel($channel)
	{
		if (isset($this->deniedChannels[$channel])) {
			throw InvalidArgumentException("Channel with name '$channel' is already denied.");
		} else if (!isset($this->allowedChannels[$channel])) {
			throw InvalidArgumentException("Channel with name '$channel' isn't defined.");
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
	 * @return provides a fluent interface
	 * @throws \Nette\InvalidArgumentException on channel name conflict
	 */
	public function addChannel($channel, $title, $image = NULL)
	{
		if (isset($this->allowedChannels[$channel]) || isset($this->deniedChannels[$channel])) {
			throw InvalidArgumentException("Channel with name '$channel' is already defined.");
		}

		$this->allowedChannels[$channel] = (object) array(
			'title' => $title,
		);

		if (isset($image)) {
			$this->allowedChannels[$channel]->image = $image;
		}

		return $this;
	}


	/**
	 * Returns list of allowed payment channels
	 * 
	 * @return array
	 */
	public function getChannels()
	{
		return $this->allowedChannels;
	}


	/**
	 * Setups default set of payment channels
	 */
	protected function setupChannels()
	{
		foreach (array(
			self::CARD_VISA => array(
				'image' => 'gopay_payment_cards.gif',
				'title' => 'Zaplatit GoPay - Platební karty MasterCard, Maestro a Visa',
			),
			self::MPENIZE => array(
				'image' => 'gopay_payment_mpenize.gif',
				'title' => 'Zaplatit GoPay - mPeníze',
			),
			self::EPLATBY => array(
				'image' => 'gopay_payment_eplatby.gif',
				'title' => 'Zaplatit GoPay - ePlatby',
			),
			self::MOJE_PLATBA => array(
				'image' => 'gopay_payment_mojeplatba.gif',
				'title' => 'Zaplatit GoPay - MojePlatba',
			),
			self::BANK => array(
				'image' => 'gopay_payment_bank.gif',
				'title' => 'Zaplatit GoPay - platební karty',
			),
			self::PURSE => array(
				'image' => 'gopay_payment_gopay.gif',
				'title' => 'Zaplatit GoPay - GoPay peněženka',
			),
			self::MONEYBOOKERS => array(
				'image' => 'gopay_payment_moneybookers.gif',
				'title' => 'Zaplatit GoPay - MoneyBookers',
			),
			self::SUPERCASH => array(
				'image' => 'gopay_payment_supercash.gif',
				'title' => 'Zaplatit GoPay - SUPERCASH',
			),
		) as $name => $channel) {
			$this->addChannel($name, $channel['title'], $channel['image']);
		}
	}

/* === Payments ============================================================= */

	/**
	 * Creates new Payment with given default values
	 * 
	 * @param  array
	 * @return \Gopay\Payment
	 */
	public function createPayment($values = array())
	{
		return new Payment($this, $this->getIdentification(), (array) $values);
	}


	/**
	 * Executes payment via redirecting to GoPay payment gate
	 * 
	 * @param  \Gopay\Payment
	 * @param  string
	 * @param  callback
	 * @return \Nette\Application\Responses\RedirectResponse
	 * @throws \Nette\InvalidArgumentException on undefined channel or provided ReturnedPayment
	 * @throws \Gopay\GopayFatalException on maldefined parameters
	 * @throws \Gopay\GopayException on failed communication with WS
	 */
	public function pay(Payment $payment, $channel, $callback = NULL)
	{
		error_reporting(E_ALL ^ E_NOTICE);

		if ($payment instanceof ReturnedPayment) {
			throw new InvalidArgumentException("Cannot use instance of 'ReturnedPayment'! This payment has been already used for paying");
		}

		if (!isset($this->allowedChannels[$channel])) {
			throw new InvalidArgumentException("Payment channel '$channel' is not supported");
		}

		if ($channel == self::CARD_VISA || $channel == self::CARD_EXPRES) {
			$customer = $payment->getCustomer();
			$id = GopaySoap::createCustomerEshopPayment(
				$this->goId,
				$payment->getProduct(),
				$payment->getSum() * 100, // given in cents
				$payment->getSpecific(),
				$this->success,
				$this->failure,
				$this->secretKey,
				array_keys($this->allowedChannels),
				// customer info
				$customer->firstName,
				$customer->lastName,
				$customer->city,
				$customer->street,
				$customer->postalCode,
				$customer->countryCode,
				$customer->email,
				$customer->phoneNumber
			);
		} else {
			$id = GopaySoap::createEshopPayment(
				$this->goId,
				$payment->getProduct(),
				$payment->getSum() * 100, // given in cents
				$payment->getSpecific(),
				$this->success,
				$this->failure,
				$this->secretKey,
				array_keys($this->allowedChannels)
			);
		}

		if ($id === -1) {
			throw new GopayFatalException("Execution of payment failed due to invalid parameters.");
		} else if ($id === -2) {
			throw new GopayException("Execution of payment failed due to communication with WS.");
		}

		$payment->setId($id);

		$url = GopayHelper::fullIntegrationURL()
				. "?sessionInfo.eshopGoId=" . $this->goId
				. "&sessionInfo.paymentSessionId=" . $id
				. "&sessionInfo.encryptedSignature=" . $this->createSignature($id)
				. "&paymentChannel=" . $channel;

		if (isset($callback)) {
			call_user_func_array($callback, array($id));
		}

		return new RedirectResponse($url);
	}


	/**
	 * Returns payment after visiting Payment Gate
	 *
	 * @param  array
	 * @param  array
	 * @return \Gopay\Payment
	 */
	public function restorePayment($values, $valuesToBeVerified)
	{
		return new ReturnedPayment($this, $this->getIdentification(), (array) $values, (array) $valuesToBeVerified);
	}


	/**
	 * Creates encrypted signature for given given payment session id
	 * 
	 * @param  int
	 * @return string
	 */
	private function createSignature($paymentId)
	{
		return GopayHelper::encrypt(GopayHelper::hash(
			GopayHelper::concatPaymentSession(
				$this->goId,
				$paymentId,
				$this->secretKey
			)
		), $this->secretKey);
	}

/* === Form ================================================================= */

	/**
	 * Binds form to Gopay
	 * - adds payment buttons
	 *
	 * @param  \Nette\Forms\Form
	 * @param  array|callable
	 */
	public function bindForm(Form $form, $callbacks)
	{
		foreach ($this->allowedChannels as $name => $channel) {
			if (!isset($channel->image)) {
				$button = $form['gopayChannel' . $name] = new PaymentButton($name, $channel->title);
			} else {
				$button = $form['gopayChannel' . $name] = new ImagePaymentButton($name, $this->imagePath . '/' . $channel->image, $channel->title);
			}

			if (!is_array($callbacks)) $callbacks = array($callbacks);
			foreach ($callbacks as $callback) {
				$button->onClick[] = $callback;
			}

			$this->allowedChannels[$name]->control = 'gopayChannel' . $name;
		}
	}

}

class GopayFatalException extends \Exception {}

class GopayException extends GopayFatalException {}
