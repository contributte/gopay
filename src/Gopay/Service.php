<?php

namespace Markette\Gopay;

use Exception;
use InvalidArgumentException;
use Markette\Gopay\Api\GopayConfig;
use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Nette;
use Nette\Application\Responses\RedirectResponse;
use Nette\DI;
use Nette\Forms;
use Nette\Utils\Callback;
use stdClass;

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

    // METHODS =================================================================

    /** @const Platba kartou - Komerční banka, a.s. - Global Payments */
    const METHOD_CARD_GPKB = 'eu_gp_kb';

    /** @const Platba kartou - GoPay - platební karty B */
    const METHOD_CARD_GPB = 'eu_om';

    /** @const Paysafecard - kupón */
    const METHOD_PAYSAFECARD = 'eu_psc';

    /** @const Elektronická peněženka PayPal */
    const METHOD_PAYPAL = 'eu_paypal';

    /** @const Terminály České pošty, s.p. a spol. Sazka, a.s. */
    const METHOD_SUPERCASH = 'SUPERCASH';

    /** @const Mobilní telefon - Premium SMS */
    const METHOD_PREMIUMSMS = 'eu_pr_sms';

    /** @const Mobilní telefon - platební brána operátora */
    const METHOD_MPLATBA = 'cz_mp';

    /** @const Platební tlačítko - Platba KB - Mojeplatba - Internetové bankovnictví Komerční banky a.s. */
    const METHOD_KOMERCNIB = 'cz_kb';

    /** @const Platební tlačítko - Platba RB - ePlatby - Internetové bankovnictví Raiffeisenbank a.s. */
    const METHOD_RAIFFEISENB = 'cz_rb';

    /** @const Platební tlačítko - Platba mBank - mPeníze - Internetové bankovnictví MBank */
    const METHOD_MBANK = 'cz_mb';

    /** @const Platební tlačítko - Platba Fio Banky - Internetové bankovnictví Fio banky */
    const METHOD_FIOB = 'cz_fb';

    /** @const Platební tlačítko - Platba Česká spořitelna - Internetové bankovnictví České spořitelny */
    const METHOD_CSAS = 'cz_csas';

    /** @const Běžný bankovní převod */
    const METHOD_TRANSFER = 'eu_bank';

    /** @const Gopay - Elektronická peněženka. */
    const METHOD_GOPAY = 'eu_gp_w';

    /** @const Platební tlačítko - Platba UniCredit Bank - uniplatba - Internetové bankovnictví UniCredit Bank a.s. */
    const METHOD_SK_UNICREDITB = 'sk_uni';

    /** @const Platební tlačítko - Platba SLSP - sporopay - Internetové bankovnictví Slovenská sporiteľňa, a. s. */
    const METHOD_SK_SLOVENSKAS = 'sk_sp';

    /** @const Platební tlačítko - Platba Všeobecná úverová banka - Internetové bankovnictví Všeobecná úverová banka, a.s. */
    const METHOD_SK_VUB = 'sk_vubbank';

    /** @const Platební tlačítko - Platba Tatra banka - Internetové bankovnictví Tatra banka a.s. */
    const METHOD_SK_TATRA = 'sk_tatrabank';

    /** @const Platební tlačítko - Platba Poštová banka - Internetové bankovnictví Poštová banka a.s. */
    const METHOD_SK_PAB = 'sk_pabank';

    /** @const Platební tlačítko - Platba Sberbank Slovensko - Internetové bankovnictví Sberbank Slovensko, a.s. */
    const METHOD_SK_SBERB = 'sk_sberbank';

    /** @const Platební tlačítko - Platba Československá obchodná banka - Internetová bankovnictví Československá obchodná banka, a.s. */
    const METHOD_SK_CSOB = 'sk_csob';

    /** @const Platební tlačítko - Platba OTP banka Slovensko, a.s. - Internetové bankovnictví OTP banka Slovensko, a.s. */
    const METHOD_SK_OPTB = 'sk_otpbank';

    /** @const Platbu vybere uživatel */
    const METHOD_USER_SELECT = NULL;

    // CURRENCIES ==============================================================

    /** @const Czech koruna */
    const CURRENCY_CZK = 'CZK';

    /** @const Euro */
    const CURRENCY_EUR = 'EUR';

    // LANGUAGES ===============================================================

    /** @const Czech */
    const LANG_CS = 'CS';
    /** @const English */
    const LANG_EN = 'EN';

    // =========================================================================

    /** @var GopaySoap */
    private $soap;

    /** @var float */
    private $gopayId;

    /** @var string */
    private $gopaySecretKey;

    /** @var bool */
    private $testMode = FALSE;

    /** @var bool */
    private $changeChannel;

    /** @var string */
    private $lang = self::LANG_CS;

    /** @var string */
    private $successUrl;

    /** @var string */
    private $failureUrl;

    /** @var array */
    private $channels = [];

    /** @var array */
    private $allowedLang = [
        self::LANG_CS,
        self::LANG_EN,
    ];

    /**
     * Service constructor.
     *
     * @param GopaySoap $soap
     * @param float $gopayId
     * @param string $gopaySecretKey
     * @param bool $testMode
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
     * @param float $id
     * @return self
     */
    public function setGopayId($id)
    {
        $this->gopayId = (float)$id;
        return $this;
    }

    /**
     * Sets Gopay secret key
     *
     * @param string $secretKey
     * @return self
     */
    public function setGopaySecretKey($secretKey)
    {
        $this->gopaySecretKey = (string)$secretKey;
        return $this;
    }

    /**
     * Sets state of test mode
     *
     * @param bool $testMode
     * @return self
     */
    public function setTestMode($testMode = TRUE)
    {
        $this->testMode = (bool)$testMode;
        GopayConfig::$version = $this->testMode ? GopayConfig::TEST : GopayConfig::PROD;
        return $this;
    }

    /**
     * @param bool $changeChannel
     * @return $this
     */
    public function setChangeChannel($changeChannel = TRUE)
    {
        $this->changeChannel = (bool)$changeChannel;
        return $this;
    }

    /**
     * Sets payment gateway language
     *
     * @param string $lang
     * @throws InvalidArgumentException if language is not supported
     * @return self
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
     * @param string $url
     * @return self
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
     * @param string
     * @return self
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
     * @return self
     */
    public function addChannel($code, $name, $logo = NULL, $offline = NULL, $description = NULL, array $params = [])
    {
        if (isset($this->channels[$code])) {
            throw new InvalidArgumentException("Channel with name '$code' is already defined.");
        }

        $this->channels[$code] = (object)array_merge($params, [
            'code' => $code,
            'name' => $name,
            'logo' => $logo,
            'offline' => $offline,
            'description' => $description,
        ]);

        return $this;
    }

    /*
     * Returns list of payment channels
     *
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Creates new Payment with given default values
     *
     * @param array $values
     * @return Payment
     */
    public function createPayment(array $values = [])
    {
        return new Payment($values);
    }

    /**
     * Returns payment after visiting Payment Gate
     *
     * @param array $values
     * @param array $valuesToBeVerified
     * @return ReturnedPayment
     */
    public function restorePayment($values, $valuesToBeVerified)
    {
        return new ReturnedPayment($values, $this->gopayId, $this->gopaySecretKey, (array)$valuesToBeVerified);
    }

    /**
     * Check and create payment
     *
     * @param Payment $payment
     * @param string $channel
     * @return int
     * @throws InvalidArgumentException on undefined channel or provided ReturnedPayment
     * @throws GopayFatalException on maldefined parameters
     * @throws GopayException on failed communication with WS
     */
    protected function createPaymentInternal(Payment $payment, $channel)
    {
        if ($payment instanceof ReturnedPayment) {
            throw new InvalidArgumentException("Cannot use instance of 'ReturnedPayment'! This payment has been already used for paying");
        }

        if (!isset($this->channels[$channel]) && $channel !== self::METHOD_USER_SELECT) {
            throw new InvalidArgumentException("Payment channel '$channel' is not supported");
        }

        if ($this->changeChannel === TRUE) {
            $channels = array_keys($this->channels);
        } else {
            $channels = [$channel];
        }

        try {
            $customer = $payment->getCustomer();
            $paymentSessionId = $this->soap->createPayment(
                $this->gopayId,
                $payment->getProductName(),
                $payment->getSumInCents(),
                $payment->getCurrency(),
                $payment->getVariable(),
                $this->successUrl,
                $this->failureUrl,
                $channels,
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

            return $paymentSessionId;
        } catch (Exception $e) {
            throw new GopayException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Executes payment via redirecting to GoPay payment gate
     *
     * @param Payment $payment
     * @param string $channel
     * @param callable $callback
     * @return RedirectResponse
     * @throws InvalidArgumentException on undefined channel or provided ReturnedPayment
     * @throws GopayFatalException on maldefined parameters
     * @throws GopayException on failed communication with WS
     */
    public function pay(Payment $payment, $channel, $callback)
    {
        $paymentSessionId = $this->createPaymentInternal($payment, $channel);

        $url = GopayConfig::fullIntegrationURL()
            . "?sessionInfo.targetGoId=" . $this->gopayId
            . "&sessionInfo.paymentSessionId=" . $paymentSessionId
            . "&sessionInfo.encryptedSignature=" . $this->createSignature($paymentSessionId);

        Callback::invokeArgs($callback, [$paymentSessionId]);

        return new RedirectResponse($url);
    }

    /**
     * Executes payment via INLINE GoPay payment gate
     *
     * @param Payment $payment
     * @param string $channel
     * @param callable $callback
     * @return RedirectResponse
     * @throws InvalidArgumentException on undefined channel or provided ReturnedPayment
     * @throws GopayFatalException on maldefined parameters
     * @throws GopayException on failed communication with WS
     */
    public function payInline(Payment $payment, $channel, $callback)
    {
        $paymentSessionId = $this->createPaymentInternal($payment, $channel);

        $response = [
            "url" => GopayConfig::fullNewIntegrationURL() . '/' . $paymentSessionId,
            "signature" => $this->createSignature($paymentSessionId)
        ];

        Nette\Utils\Callback::invokeArgs($callback, [$paymentSessionId]);

        return $response;
    }

    /**
     * Binds payment buttons fo form
     *
     * @param Forms\Container $container
     * @param array|callable $callbacks
     */
    public function bindPaymentButtons(Forms\Container $container, $callbacks)
    {
        foreach ($this->channels as $channel) {
            $this->bindPaymentButton($channel, $container, $callbacks);
        }
    }


    /**
     * Binds form to Gopay
     * - adds one payment button for given channel
     *
     * @param string|stdClass $channel
     * @param Forms\Container $container
     * @param array|callable
     * @throws InvalidArgumentException
     * @return IPaymentButton
     */
    public function bindPaymentButton($channel, Forms\Container $container, $callbacks = [])
    {
        if (!$channel instanceof stdClass) {
            if (!isset($this->channels[$channel])) {
                throw new InvalidArgumentException("Channel '$channel' is not allowed.");
            }
            $channel = $this->channels[$channel];
        }

        if (!isset($channel->logo)) {
            $button = $container['gopayChannel' . $channel->code] = new PaymentButton($channel->code, $channel->name);
        } else {
            $button = $container['gopayChannel' . $channel->code] = new ImagePaymentButton($channel->code, $channel->logo, $channel->name);
        }

        $channel->control = 'gopayChannel' . $channel->code;

        if (!is_array($callbacks)) $callbacks = [$callbacks];
        foreach ($callbacks as $callback) {
            $button->onClick[] = $callback;
        }

        return $button;
    }


    /**
     * Creates encrypted signature for given given payment session id
     *
     * @param int $paymentSessionId
     * @return string
     */
    private function createSignature($paymentSessionId)
    {
        return GopayHelper::encrypt(
            GopayHelper::hash(
                GopayHelper::concatPaymentSession(
                    (float)$this->gopayId,
                    (float)$paymentSessionId,
                    $this->gopaySecretKey
                )
            ),
            $this->gopaySecretKey
        );
    }

    /**
     * Registers 'addPaymentButtons' & 'addPaymentButton' methods to form using DI container
     *
     * @param DI\Container $dic
     * @param string $serviceName
     */
    public static function registerAddPaymentButtonsUsingDependencyContainer(DI\Container $dic, $serviceName)
    {
        Forms\Container::extensionMethod('addPaymentButtons', function ($container, $callbacks) use ($dic, $serviceName) {
            $dic->getService($serviceName)->bindPaymentButtons($container, $callbacks);
        });
        Forms\Container::extensionMethod('addPaymentButton', function ($container, $channel) use ($dic, $serviceName) {
            $dic->getService($serviceName)->bindPaymentButton($channel, $container);
        });
    }

    /**
     * Registers 'addPaymentButtons' & 'addPaymentButton' methods to form
     *
     * @param Service $service
     */
    public static function registerAddPaymentButtons(Service $service)
    {
        Forms\Container::extensionMethod('addPaymentButtons', function ($container, $callbacks) use ($service) {
            $service->bindPaymentButtons($container, $callbacks);
        });
        Forms\Container::extensionMethod('addPaymentButton', function ($container, $channel) use ($service) {
            return $service->bindPaymentButton($channel, $container);
        });
    }

}
