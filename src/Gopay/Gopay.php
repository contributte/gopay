<?php declare(strict_types = 1);

namespace Markette\Gopay;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Nette\SmartObject;

/**
 * Base Gopay class
 *
 * @property-read Config $config
 * @property-read GopaySoap $soap
 * @property-read GopayHelper $helper
 */
class Gopay
{

	use SmartObject;

	// METHODS =================================================================

	/** @const Platba kartou - Komerční banka, a.s. - Global Payments */
	public const METHOD_CARD_GPKB = 'eu_gp_kb';

	/** @const Platba kartou - GoPay - platební karty B */
	public const METHOD_CARD_GPB = 'eu_om';

	/** @const Paysafecard - kupón */
	public const METHOD_PAYSAFECARD = 'eu_psc';

	/** @const Elektronická peněženka PayPal */
	public const METHOD_PAYPAL = 'eu_paypal';

	/** @const Terminály České pošty, s.p. a spol. Sazka, a.s. */
	public const METHOD_SUPERCASH = 'SUPERCASH';

	/** @const Mobilní telefon - Premium SMS */
	public const METHOD_PREMIUMSMS = 'eu_pr_sms';

	/** @const Mobilní telefon - platební brána operátora */
	public const METHOD_MPLATBA = 'cz_mp';

	/** @const Platební tlačítko - Platba KB - Mojeplatba - Internetové bankovnictví Komerční banky a.s. */
	public const METHOD_KOMERCNIB = 'cz_kb';

	/** @const Platební tlačítko - Platba RB - ePlatby - Internetové bankovnictví Raiffeisenbank a.s. */
	public const METHOD_RAIFFEISENB = 'cz_rb';

	/** @const Platební tlačítko - Platba mBank - mPeníze - Internetové bankovnictví MBank */
	public const METHOD_MBANK = 'cz_mb';

	/** @const Platební tlačítko - Platba Fio Banky - Internetové bankovnictví Fio banky */
	public const METHOD_FIOB = 'cz_fb';

	/** @const Platební tlačítko - Platba Česká spořitelna - Internetové bankovnictví České spořitelny */
	public const METHOD_CSAS = 'cz_csas';

	/** @const Běžný bankovní převod */
	public const METHOD_TRANSFER = 'eu_bank';

	/** @const Gopay - Elektronická peněženka. */
	public const METHOD_GOPAY = 'eu_gp_w';

	/** @const Platební tlačítko - Platba UniCredit Bank - uniplatba - Internetové bankovnictví UniCredit Bank a.s. */
	public const METHOD_SK_UNICREDITB = 'sk_uni';

	/** @const Platební tlačítko - Platba SLSP - sporopay - Internetové bankovnictví Slovenská sporiteľňa, a. s. */
	public const METHOD_SK_SLOVENSKAS = 'sk_sp';

	/** @const Platební tlačítko - Platba Všeobecná úverová banka - Internetové bankovnictví Všeobecná úverová banka, a.s. */
	public const METHOD_SK_VUB = 'sk_vubbank';

	/** @const Platební tlačítko - Platba Tatra banka - Internetové bankovnictví Tatra banka a.s. */
	public const METHOD_SK_TATRA = 'sk_tatrabank';

	/** @const Platební tlačítko - Platba Poštová banka - Internetové bankovnictví Poštová banka a.s. */
	public const METHOD_SK_PAB = 'sk_pabank';

	/** @const Platební tlačítko - Platba Sberbank Slovensko - Internetové bankovnictví Sberbank Slovensko, a.s. */
	public const METHOD_SK_SBERB = 'sk_sberbank';

	/** @const Platební tlačítko - Platba Československá obchodná banka - Internetová bankovnictví Československá obchodná banka, a.s. */
	public const METHOD_SK_CSOB = 'sk_csob';

	/** @const Platební tlačítko - Platba OTP banka Slovensko, a.s. - Internetové bankovnictví OTP banka Slovensko, a.s. */
	public const METHOD_SK_OPTB = 'sk_otpbank';

	/** @const Platbu vybere uživatel */
	public const METHOD_USER_SELECT = null;

	// CURRENCIES ==============================================================

	/** @const Czech koruna */
	public const CURRENCY_CZK = 'CZK';

	/** @const Euro */
	public const CURRENCY_EUR = 'EUR';

	/** @const Polish zloty */
	public const CURRENCY_PLN = 'PLN';

	/** @const Hungarian forint */
	public const CURRENCY_HUF = 'HUF';

	/** @const British pound */
	public const CURRENCY_GBP = 'GBP';

	/** @const US dollar */
	public const CURRENCY_USD = 'USD';

	// LANGUAGES ===============================================================

	/** @const Czech */
	public const LANG_CS = 'CS';
	/** @const English */
	public const LANG_EN = 'EN';
	/** @const Slovak */
	public const LANG_SK = 'SK';
	/** @const German */
	public const LANG_DE = 'DE';
	/** @const Russian */
	public const LANG_RU = 'RU';

	/** @var Config */
	private $config;

	/** @var GopayHelper */
	private $helper;

	/** @var GopaySoap */
	private $soap;

	/**
	 * @param Config $config
	 * @param GopaySoap $soap
	 * @param GopayHelper $helper
	 */
	public function __construct(Config $config, GopaySoap $soap, GopayHelper $helper)
	{
		$this->config = $config;
		$this->soap = $soap;
		$this->helper = $helper;
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @return GopayHelper
	 */
	public function getHelper()
	{
		return $this->helper;
	}

	/**
	 * @return GopaySoap
	 */
	public function getSoap()
	{
		return $this->soap;
	}

}
