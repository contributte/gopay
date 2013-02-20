<?php

namespace Markette\Gopay\Api;


/**
 *  Konfiguracni trida pro ziskavani URL pro praci s platbami
 */
class GopayConfig
{

	const TEST = "TEST";
	const PROD = "PROD";

	/**
	 * Parametr specifikujici, pracuje-li se na testovacim ci provoznim prostredi
	 */
	static $version = self::TEST;

	/**
	 * Nastaveni testovaciho ci provozniho prostredi prostrednictvim parametru
	 *
	 * @param $new_version
	 * TEST - Testovaci prostredi
	 * PROD - Provozni prostredi
	 */
	public static function init($new_version)
	{
		self::$version = $new_version;
	}

	/**
	 * URL platebni brany pro uplnou integraci
	 *
	 * @return URL
	 */
	public static function fullIntegrationURL()
	{

		if (self::$version == self::PROD) {
			return "https://gate.gopay.cz/gw/pay-full-v2";

		} else {
			return "https://testgw.gopay.cz/gw/pay-full-v2";

		}
	}

	/**
	 * URL webove sluzby GoPay
	 *
	 * @return URL - wsdl
	 */
	public static function ws()
	{
		if (self::$version == self::PROD) {
			return "https://gate.gopay.cz/axis/EPaymentServiceV2?wsdl";

		} else {
			return "https://testgw.gopay.cz/axis/EPaymentServiceV2?wsdl";

		}
	}

	/**
	 * URL platebni brany pro zakladni integraci
	 *
	 * @return URL
	 */
	public static function baseIntegrationURL()
	{
		if (self::$version == self::PROD) {
			return "https://gate.gopay.cz/gw/pay-base-v2";

		} else {
			return "https://testgw.gopay.cz/gw/pay-base-v2";

		}
	}

	/**
	 * URL pro stazeni vypisu plateb uzivatele
	 *
	 * @return URL
	 */
	public static function getAccountStatementURL()
	{
		if (self::$version == GopayConfig::PROD) {
			return "https://gate.gopay.cz/gw/services/get-account-statement";

		} else {
			return "https://testgw.gopay.cz/gw/services/get-account-statement";

		}
	}

}
