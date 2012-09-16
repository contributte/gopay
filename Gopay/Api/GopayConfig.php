<?php

namespace Markette\Gopay\Api;


class GopayConfig {
	
	/**
	 *  Konfiguracni trida pro ziskavani URL pro praci s platbami
	 *  
	 */

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
	 *
	 */
	public static function init($new_version) {
		self::$version = $new_version;
	}
	
	/**
	 * URL platebni brany pro uplnou integraci
	 *
	 * @return URL
	 */
	public static function fullIntegrationURL() {
		
		if (self::$version == self::PROD) {
			return 'https://gate.gopay.cz/zaplatit-plna-integrace';		
			
		} else {
			return 'https://testgw.gopay.cz/zaplatit-plna-integrace';		
			
		}
	}

	/**
	 * URL webove sluzby GoPay
	 *
	 * @return URL - wsdl
	 */
	public static function ws() {
		
		if (self::$version == self::PROD) {
			return 'https://gate.gopay.cz/axis/EPaymentService?wsdl';		
			
		} else {
			return 'https://testgw.gopay.cz/axis/EPaymentService?wsdl';	
			
		}
	}		

	
	/**
	 * URL platebni brany pro zakladni integraci
	 *
	 * @return URL
	 */
	public static function baseIntegrationURL() {
		
		if (self::$version == self::PROD) {
			return 'https://gate.gopay.cz/zaplatit-jednoducha-integrace';	
			
		} else {
			return 'https://testgw.gopay.cz/zaplatit-jednoducha-integrace';
			
		}
	}

	/**
	 * URL platebni brany pro platebni tlacitko
	 *
	 * @return URL
	 */
	public static function buyerBaseIntegrationURL() {
		
		if (self::$version == self::PROD) {
			return 'https://gate.gopay.cz/zaplatit-tlacitko';
			
		} else {
			return 'https://testgw.gopay.cz/zaplatit-tlacitko';
			
		}
	}

}
