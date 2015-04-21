<?php

namespace Markette\Gopay\Api;

use \SoapClient;
use \SoapFault;
use \Exception;



class GopaySoap {

	/**
	 * Vytvoreni opakovane platby
	 *
	 * @param long $targetGoId - identifikator prijemce - GoId
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param int 	 $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $currency - mena, ve ktere platba probiha
	 * @param string $orderNumber - identifikator objednavky
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param string $recurrenceDateTo - datum, do nehoz budou provadeny opakovane platby. Jedna se textovy retezec ve formatu yyyy-MM-dd.
	 * @param string $recurrenceCycle - zakladni casovou jednotku opakovani. Nabyva hodnot [DAY, WEEK, MONTH], pro opakování od CS a.s. lze pouzit pouze hodnotu DAY.
	 * @param int 	 $recurrencePeriod - definuje periodu opakovane platby. Napr. při konfiguraci DAY,5 bude platba provadena kazdy 5. den
	 * @param string $paymentChannels - pole platebnich kanalu, ktere se zobrazi na platebni brane
	 * @param string $defaultPaymentChannel - platebni kanal, ktery se zobrazi (predvybere) na platebni brane po presmerovani
	 * @param string $secureKey - kryptovaci klic prideleny prijemci
	 *
	 * Informace o zakaznikovi
	 * @param string $firstName   - Jmeno zakaznika
	 * @param string $lastName    - Prijmeni
	 *
	 * Adresa
	 * @param string $city        - Mesto
	 * @param string $street      - Ulice
	 * @param string $postalCode  - PSC
	 * @param string $countryCode - Kod zeme. Validni kody jsou uvedeny ve tride CountryCode
	 * @param string $email       - Email zakaznika
	 * @param string $phoneNumber - Tel. cislo
	 *
	 * @param string $p1 - $p4 - volitelne parametry (max. 128 znaku).
	 * @param string $lang - jazyk plat. brany
	 * Parametry jsou vraceny v nezmenene podobe jako soucast volani dotazu na stav platby $paymentStatus (viz metoda isPaymentDone)
	 *
	 * @return paymentSessionId
	 */
	public static function createRecurrentPayment($targetGoId,
		$productName,
		$totalPriceInCents,
		$currency,
		$orderNumber,
		$successURL,
		$failedURL,
		$recurrenceDateTo,
		$recurrenceCycle,
		$recurrencePeriod,
		$paymentChannels,
		$defaultPaymentChannel,
		$secureKey,
		$firstName,
		$lastName,
		$city,
		$street,
		$postalCode,
		$countryCode,
		$email,
		$phoneNumber,
		$p1,
		$p2,
		$p3,
		$p4,
		$lang) {

		return  GopaySoap::createBasePayment($targetGoId,
			$productName,
			$totalPriceInCents,
			$currency,
			$orderNumber,
			$successURL,
			$failedURL,
			false,
			true,
			$recurrenceDateTo,
			$recurrenceCycle,
			$recurrencePeriod,
			$paymentChannels,
			$defaultPaymentChannel,
			$secureKey,
			$firstName,
			$lastName,
			$city,
			$street,
			$postalCode,
			$countryCode,
			$email,
			$phoneNumber,
			$p1,
			$p2,
			$p3,
			$p4,
			$lang);
	}

	/**
	 * Vytvoreni predautorizovane platby
	 *
	 * @param long 	 $targetGoId - identifikator prijemce - GoId
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param int 	 $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $currency - mena, ve ktere platba probiha
	 * @param string $orderNumber - identifikator objednavky
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param string $paymentChannels - pole platebnich kanalu, ktere se zobrazi na platebni brane
	 * @param string $defaultPaymentChannel - platebni kanal, ktery se zobrazi (predvybere) na platebni brane po presmerovani
	 * @param string $secureKey - kryptovaci klic prideleny prijemci
	 *
	 * Informace o zakaznikovi
	 * @param string $firstName   - Jmeno zakaznika
	 * @param string $lastName    - Prijmeni
	 *
	 * Adresa
	 * @param string $city        - Mesto
	 * @param string $street      - Ulice
	 * @param string $postalCode  - PSC
	 * @param string $countryCode - Kod zeme. Validni kody jsou uvedeny ve tride CountryCode
	 * @param string $email       - Email zakaznika
	 * @param string $phoneNumber - Tel. cislo
	 *
	 * @param string $p1 - $p4 - volitelne parametry (max. 128 znaku).
	 * @param string $lang - jazyk plat. brany
	 * Parametry jsou vraceny v nezmenene podobe jako soucast volani dotazu na stav platby $paymentStatus (viz metoda isPaymentDone)
	 *
	 * @return paymentSessionId
	 */
	public static function createPreAutorizedPayment($targetGoId,
		$productName,
		$totalPriceInCents,
		$currency,
		$orderNumber,
		$successURL,
		$failedURL,
		$paymentChannels,
		$defaultPaymentChannel,
		$secureKey,
		$firstName,
		$lastName,
		$city,
		$street,
		$postalCode,
		$countryCode,
		$email,
		$phoneNumber,
		$p1,
		$p2,
		$p3,
		$p4,
		$lang) {

		return  GopaySoap::createBasePayment($targetGoId,
			$productName,
			$totalPriceInCents,
			$currency,
			$orderNumber,
			$successURL,
			$failedURL,
			true,
			false,
			null,
			null,
			null,
			$paymentChannels,
			$defaultPaymentChannel,
			$secureKey,
			$firstName,
			$lastName,
			$city,
			$street,
			$postalCode,
			$countryCode,
			$email,
			$phoneNumber,
			$p1,
			$p2,
			$p3,
			$p4,
			$lang);
	}

	/**
	 * Vytvoreni standardni platby
	 *
	 * @param long 	 $targetGoId - identifikator prijemce - GoId
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param int 	 $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $currency - mena, ve ktere platba probiha
	 * @param string $orderNumber - identifikator objednavky
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param string $paymentChannels - pole platebnich kanalu, ktere se zobrazi na platebni brane
	 * @param string $defaultPaymentChannel - platebni kanal, ktery se zobrazi (predvybere) na platebni brane po presmerovani
	 * @param string $secureKey - kryptovaci klic prideleny prijemci
	 *
	 * Informace o zakaznikovi
	 * @param string $firstName   - Jmeno zakaznika
	 * @param string $lastName    - Prijmeni
	 *
	 * Adresa
	 * @param string $city        - Mesto
	 * @param string $street      - Ulice
	 * @param string $postalCode  - PSC
	 * @param string $countryCode - Kod zeme. Validni kody jsou uvedeny ve tride CountryCode
	 * @param string $email       - Email zakaznika
	 * @param string $phoneNumber - Tel. cislo
	 *
	 * @param string $p1 - $p4 - volitelne parametry (max. 128 znaku).
	 * @param string $lang - jazyk plat. brany
	 * Parametry jsou vraceny v nezmenene podobe jako soucast volani dotazu na stav platby $paymentStatus (viz metoda isPaymentDone)
	 *
	 * @return paymentSessionId
	 */
	public static function createPayment($targetGoId,
		$productName,
		$totalPriceInCents,
		$currency,
		$orderNumber,
		$successURL,
		$failedURL,
		$paymentChannels,
		$defaultPaymentChannel,
		$secureKey,
		$firstName,
		$lastName,
		$city,
		$street,
		$postalCode,
		$countryCode,
		$email,
		$phoneNumber,
		$p1,
		$p2,
		$p3,
		$p4,
		$lang) {

		return  GopaySoap::createBasePayment($targetGoId,
			$productName,
			$totalPriceInCents,
			$currency,
			$orderNumber,
			$successURL,
			$failedURL,
			false,
			false,
			null,
			null,
			null,
			$paymentChannels,
			$defaultPaymentChannel,
			$secureKey,
			$firstName,
			$lastName,
			$city,
			$street,
			$postalCode,
			$countryCode,
			$email,
			$phoneNumber,
			$p1,
			$p2,
			$p3,
			$p4,
			$lang);
	}

	/**
	 * Vytvoreni platby s udaji o zakaznikovi pomoci WS z eshopu
	 *
	 * @param long $targetGoId - identifikator prijemce - GoId
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param int $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $currency - mena, ve ktere platba probiha
	 * @param string $orderNumber - identifikator objednavky
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param boolean $preAuthorization - jedna-li se o predautorizovanou platbu
	 * @param boolean $recurrentPayment - jedna-li se o opakovanou platbu
	 * @param string $recurrenceDateTo - datum, do nehoz budou provadeny opakovane platby. Jedna se textovy retezec ve formatu yyyy-MM-dd.
	 * @param string $recurrenceCycle - zakladni casovou jednotku opakovani. Nabyva hodnot [DAY, WEEK, MONTH], pro opakování od CS a.s. lze pouzit pouze hodnotu DAY.
	 * @param int 	 $recurrencePeriod - definuje periodu opakovane platby. Napr. při konfiguraci DAY,5 bude platba provadena kazdy 5. den
	 * @param string $paymentChannels - pole platebnich kanalu, ktere se zobrazi na platebni brane
	 * @param string $defaultPaymentChannel - platebni kanal, ktery se zobrazi (predvybere) na platebni brane po presmerovani
	 * @param string $secureKey - kryptovaci klic prideleny prijemci
	 *
	 * Informace o zakaznikovi
	 * @param string $firstName   - Jmeno zakaznika
	 * @param string $lastName    - Prijmeni
	 *
	 * Adresa
	 * @param string $city        - Mesto
	 * @param string $street      - Ulice
	 * @param string $postalCode  - PSC
	 * @param string $countryCode - Kod zeme. Validni kody jsou uvedeny ve tride CountryCode
	 * @param string $email       - Email zakaznika
	 * @param string $phoneNumber - Tel. cislo
	 *
	 * @param string $p1 - $p4 - volitelne parametry (max. 128 znaku).
	 * @param string $lang - jazyk plat. brany
	 * Parametry jsou vraceny v nezmenene podobe jako soucast volani dotazu na stav platby $paymentStatus (viz metoda isPaymentDone)
	 *
	 * @return paymentSessionId
	 */
	public static function createBasePayment($targetGoId,
		$productName,
		$totalPriceInCents,
		$currency,
		$orderNumber,
		$successURL,
		$failedURL,
		$preAuthorization,
		$recurrentPayment,
		$recurrenceDateTo,
		$recurrenceCycle,
		$recurrencePeriod,
		$paymentChannels,
		$defaultPaymentChannel,
		$secureKey,
		$firstName,
		$lastName,
		$city,
		$street,
		$postalCode,
		$countryCode,
		$email,
		$phoneNumber,
		$p1,
		$p2,
		$p3,
		$p4,
		$lang) {

		try {
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());
			$paymentChannelsString = (!empty($paymentChannels)) ? join($paymentChannels, ",") : "";

			/*
			   * Sestaveni pozadavku pro zalozeni platby
			   */
			$encryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentCommand((float)$targetGoId,
						$productName,
						(int)$totalPriceInCents,
						$currency,
						$orderNumber,
						$failedURL,
						$successURL,
						$preAuthorization,
						$recurrentPayment,
						$recurrenceDateTo,
						$recurrenceCycle,
						$recurrencePeriod,
						$paymentChannelsString,
						$secureKey)),
				$secureKey);

			$customerData = array(
				"firstName" => $firstName,
				"lastName" => $lastName,
				"city" => $city,
				"street" => $street,
				"postalCode" => $postalCode,
				"countryCode" => $countryCode,
				"email" => $email,
				"phoneNumber" => $phoneNumber);

			$paymentCommand = array(
				"targetGoId" => (float)$targetGoId,
				"productName" => trim($productName),
				"totalPrice" => (int)$totalPriceInCents,
				"currency" => trim($currency),
				"orderNumber" => trim($orderNumber),
				"failedURL" => trim($failedURL),
				"successURL" => trim($successURL),
				"preAuthorization" => GopayHelper::castString2Boolean($preAuthorization),
				"recurrentPayment" => GopayHelper::castString2Boolean($recurrentPayment),
				"recurrenceDateTo" => $recurrenceDateTo,
				"recurrenceCycle" => trim($recurrenceCycle),
				"recurrencePeriod" => $recurrencePeriod,
				"paymentChannels" => $paymentChannelsString,
				"defaultPaymentChannel" => $defaultPaymentChannel,
				"encryptedSignature" => $encryptedSignature,
				"customerData" => $customerData,
				"p1" => $p1,
				"p2" => $p2,
				"p3" => $p3,
				"p4" => $p4,
				"lang" => $lang);

			/*
		 	 * Vytvareni platby na strane GoPay prostrednictvim WS
		 	 */
			$paymentStatus = $go_client->__call('createPayment', array('paymentCommand'=>$paymentCommand));

			/*
			 * Kontrola stavu platby - musi byt ve stavu CREATED, kontrola parametru platby
			 */
			if ($paymentStatus->result == GopayHelper::CALL_COMPLETED
				&& $paymentStatus->sessionState == GopayHelper::CREATED
				&& $paymentStatus->paymentSessionId > 0) {

				return $paymentStatus->paymentSessionId;

			} else {
				throw new Exception("Create payment failed: " . $paymentStatus->resultDescription);

			}

		} catch (SoapFault $f) {
			/*
			 * Chyba pri komunikaci s WS
			 */
			throw new Exception("Communication with WS failed");
		}
	}


	/**
	 * Kontrola stavu platby eshopu
	 * - verifikace parametru z redirectu
	 * - kontrola stavu platby
	 *
	 * @param float $paymentSessionId - identifikator platby
	 * @param float $targetGoId - identifikator prijemnce - GoId
	 * @param string $orderNumber - identifikator objednavky
	 * @param int $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $currency - mena, ve ktere platba probiha
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param string $secureKey - kryptovaci klic pridelene GoPay
	 *
	 * @return $result
	 *  $result["paymentSessionId"] - paymentSessionId platby
	 *  $result["parentPaymentSessionId"] = paymentSessionId rodicovske platby, pokud je platba opakovana
	 *  $result["targetGoId"] - GoID eshopu
	 *  $result["productName"] - popis objednavkz
	 *  $result["orderNumber"] -cislo objednavky
	 *  $result["totalPrice"] - celkova cena v halerich
	 *  $result["currency"] - mena platby
	 *  $result["sessionState"] - stav platby
	 *  $result["sessionSubState"] - podprobnejsi popis stavu platby
	 *  $result["sessionSubStateDesc"] - textový popis doplňující informace.
	 *  $result["result"] CALL_COMPLETED – volání WS proběhlo bezchybně, CALL_FAILED – volání WS proběhlo s chybou (např.neodpovídající podpis).
	 *  $result["resultDescription"] - Popis chybové situace při CALL_FAILED.
	 *  $result["preAuthorization"] - Informuje zda byla platba zalozena jako predautorizovana
	 *  $result["recurrentPayment"] - Informuje zda byla platba zalozena jako opakovana
	 *  $result["paymentChannel"] - vybrana platebni metoda
	 *  $result["encryptedSignature"] - podpis platebniho prikazu
	 *  $result["p1"] - volitelny parametr P1;
	 *  $result["p2"] - volitelny parametr P2;
	 *  $result["p3"] - volitelny parametr P3;
	 *  $result["p4"] - volitelny parametr P4;
	 */
	public static function isPaymentDone($paymentSessionId,
		$targetGoId,
		$orderNumber,
		$totalPriceInCents,
		$currency,
		$productName,
		$secureKey) {

		try {

			/*
			 * Inicializace WS
			 */
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			/*
			   * Sestaveni dotazu na stav platby
			   */
			$sessionEncryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentSession((float)$targetGoId,
						(float)$paymentSessionId,
						$secureKey)),
				$secureKey);

			$paymentSession =  array(
				"targetGoId" => (float)$targetGoId,
				"paymentSessionId" => (float)$paymentSessionId,
				"encryptedSignature" => $sessionEncryptedSignature);

			/*
			  * Kontrola stavu platby na strane GoPay prostrednictvim WS
			  */
			$paymentStatus = $go_client->__call('paymentStatus', array('paymentSessionInfo'=>$paymentSession));

			$result = array();

			$result["paymentSessionId"] = $paymentStatus->paymentSessionId;
			$result["parentPaymentSessionId"] = $paymentStatus->parentPaymentSessionId;
			$result["targetGoId"] = $paymentStatus->targetGoId;
			$result["productName"] = $paymentStatus->productName;
			$result["orderNumber"] = $paymentStatus->orderNumber;
			$result["totalPrice"] = $paymentStatus->totalPrice;
			$result["currency"] = $paymentStatus->currency;
			$result["sessionState"] = $paymentStatus->sessionState;
			$result["sessionSubState"] = $paymentStatus->sessionSubState;
			$result["sessionSubStateDesc"] = $paymentStatus->sessionSubStateDesc;
			$result["result"] = $paymentStatus->result;
			$result["resultDescription"] = $paymentStatus->resultDescription;
			$result["preAuthorization"] = $paymentStatus->preAuthorization;
			$result["recurrentPayment"] = $paymentStatus->recurrentPayment;
			$result["paymentChannel"] = $paymentStatus->paymentChannel;
			$result["encryptedSignature"] = $paymentStatus->encryptedSignature;
			$result["p1"] = $paymentStatus->p1;
			$result["p2"] = $paymentStatus->p2;
			$result["p3"] = $paymentStatus->p3;
			$result["p4"] = $paymentStatus->p4;

			/*
			  * Kontrola zaplacenosti objednavky, verifikace parametru objednavky
			  */

			if ( $paymentStatus->result != GopayHelper::CALL_COMPLETED) {
				throw new Exception("Payment Status Call failed: " . $paymentStatus->resultDescription);
			}

			if ($result["sessionState"] != GopayHelper::PAYMENT_METHOD_CHOSEN
				&& $result["sessionState"] != GopayHelper::CREATED
				&& $result["sessionState"] != GopayHelper::PAID
				&& $result["sessionState"] != GopayHelper::AUTHORIZED
				&& $result["sessionState"] != GopayHelper::CANCELED
				&& $result["sessionState"] != GopayHelper::TIMEOUTED
				&& $result["sessionState"] != GopayHelper::REFUNDED
				&& $result["sessionState"] != GopayHelper::PARTIALLY_REFUNDED) {

				throw new Exception("Bad Payment Session State: " . $result["sessionState"]);
			}

			GopayHelper::checkPaymentStatus(
				$paymentStatus,
				$result["sessionState"],
				(float)$targetGoId,
				$orderNumber,
				(int)$totalPriceInCents,
				$currency,
				$productName,
				$secureKey);

			return $result;

		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			throw new Exception("Communication with WS failed");
		}
	}

	/**
	 * Seznam vsech aktivnich platebnich metod
	 */
	public static function paymentMethodList() {
		try {

			//inicializace WS
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			$paymentMethodsWS = $go_client->__call("paymentMethodList", array());

			$paymentMethods = new PaymentMethods();
			$paymentMethods->adapt($paymentMethodsWS);

			return $paymentMethods->paymentMethods;

		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			return null;

		}
	}

    /**
     * Kontrola stavu platby eshopu
     * - verifikace parametru z redirectu
     * - kontrola stavu platby
     *
     * @param float $paymentSessionId - identifikator platby
     * @param float $targetGoId - identifikator prijemnce - GoId
     * @param string $orderNumber - identifikator objednavky
     * @param int $totalPriceInCents - celkova cena objednavky v halerich
     * @param string $currency - mena, ve ktere platba probiha
     * @param string $productName - popis objednavky zobrazujici se na platebni brane
     * @param string $secureKey - kryptovaci klic pridelene GoPay
     *
     * @return $result
     *  $result["paymentSessionId"] - paymentSessionId platby
     *  $result["parentPaymentSessionId"] = paymentSessionId rodicovske platby, pokud je platba opakovana
     *  $result["targetGoId"] - GoID eshopu
     *  $result["productName"] - popis objednavkz
     *  $result["orderNumber"] -cislo objednavky
     *  $result["totalPrice"] - celkova cena v halerich
     *  $result["currency"] - mena platby
     *  $result["sessionState"] - stav platby
     *  $result["sessionSubState"] - podprobnejsi popis stavu platby
     *  $result["sessionSubStateDesc"] - textový popis doplňující informace.
     *  $result["result"] CALL_COMPLETED – volání WS proběhlo bezchybně, CALL_FAILED – volání WS proběhlo s chybou (např.neodpovídající podpis).
     *  $result["resultDescription"] - Popis chybové situace při CALL_FAILED.
     *  $result["preAuthorization"] - Informuje zda byla platba zalozena jako predautorizovana
     *  $result["recurrentPayment"] - Informuje zda byla platba zalozena jako opakovana
     *  $result["paymentChannel"] - vybrana platebni metoda
     *  $result["encryptedSignature"] - podpis platebniho prikazu
     *  $result["p1"] - volitelny parametr P1;
     *  $result["p2"] - volitelny parametr P2;
     *  $result["p3"] - volitelny parametr P3;
     *  $result["p4"] - volitelny parametr P4;
     *  $result["paymentInstCountryCode"] - country code vydavatele platebni karty
     *  $result["firstName"] - jmeno zakaznika
     *  $result["lastName"] - prijmeni zakaznika
     *  $result["city"] - mesto zakaznika
     *  $result["street"] - ulice zakaznika
     *  $result["postalCode"] - postovni smerovaci cislo zakaznika
     *  $result["countryCode"] - zeme zakaznika
     *  $result["email"] - email zakaznika
     *  $result["phoneNumber"] - telefoni cislo zakaznika
     */
    public static function isPaymentDoneWCust($paymentSessionId,
									          $targetGoId,
									          $orderNumber,
									          $totalPriceInCents,
									          $currency,
									          $productName,
									          $secureKey) {

        try {

			/*
			 * Inicializace WS
			 */
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			/*
			 * Sestaveni dotazu na stav platby 
			 */
			$sessionEncryptedSignature = GopayHelper::encrypt(
			    GopayHelper::hash(
			        GopayHelper::concatPaymentSession((float)$targetGoId,
						(float)$paymentSessionId,
						$secureKey)),
			    $secureKey);

			$paymentSession =  array(
			    "targetGoId" => (float)$targetGoId,
			    "paymentSessionId" => (float)$paymentSessionId,
			    "encryptedSignature" => $sessionEncryptedSignature);

			/*
			 * Kontrola stavu platby na strane GoPay prostrednictvim WS 
			 */
			$paymentStatus = $go_client->__call('paymentStatusWCust', array('paymentSessionInfo'=>$paymentSession));

			$result = array();
			$result["paymentSessionId"] = $paymentStatus->paymentSessionId;
			$result["parentPaymentSessionId"] = $paymentStatus->parentPaymentSessionId;
			$result["targetGoId"] = $paymentStatus->targetGoId;
			$result["productName"] = $paymentStatus->productName;
			$result["orderNumber"] = $paymentStatus->orderNumber;
			$result["totalPrice"] = $paymentStatus->totalPrice;
			$result["currency"] = $paymentStatus->currency;
			$result["sessionState"] = $paymentStatus->sessionState;
			$result["sessionSubState"] = $paymentStatus->sessionSubState;
			$result["sessionSubStateDesc"] = $paymentStatus->sessionSubStateDesc;
			$result["result"] = $paymentStatus->result;
			$result["resultDescription"] = $paymentStatus->resultDescription;
			$result["preAuthorization"] = $paymentStatus->preAuthorization;
			$result["recurrentPayment"] = $paymentStatus->recurrentPayment;
			$result["paymentChannel"] = $paymentStatus->paymentChannel;
			$result["encryptedSignature"] = $paymentStatus->encryptedSignature;
			$result["p1"] = $paymentStatus->p1;
			$result["p2"] = $paymentStatus->p2;
			$result["p3"] = $paymentStatus->p3;
			$result["p4"] = $paymentStatus->p4;
			$result["paymentInstCountryCode"] = $paymentStatus->paymentInstCountryCode;
			$result["firstName"] = $paymentStatus->customerData->firstName;
			$result["lastName"]  = $paymentStatus->customerData->lastName;
			$result["city"]  = $paymentStatus->customerData->city;
			$result["street"] = $paymentStatus->customerData->street;
			$result["postalCode"] = $paymentStatus->customerData->postalCode;
			$result["countryCode"] = $paymentStatus->customerData->countryCode;
			$result["email"] = $paymentStatus->customerData->email;
			$result["phoneNumber"] = $paymentStatus->customerData->phoneNumber;

			/*
			 * Kontrola zaplacenosti objednavky, verifikace parametru objednavky
			 */

			if ( $paymentStatus->result != GopayHelper::CALL_COMPLETED) {
			    throw new Exception("Payment Status Call failed: " . $paymentStatus->resultDescription);
			}

			if ($result["sessionState"] != GopayHelper::PAYMENT_METHOD_CHOSEN
			    && $result["sessionState"] != GopayHelper::CREATED
			    && $result["sessionState"] != GopayHelper::PAID
			    && $result["sessionState"] != GopayHelper::AUTHORIZED
			    && $result["sessionState"] != GopayHelper::CANCELED
			    && $result["sessionState"] != GopayHelper::TIMEOUTED
			    && $result["sessionState"] != GopayHelper::REFUNDED
			    && $result["sessionState"] != GopayHelper::PARTIALLY_REFUNDED) {

			    throw new Exception("Bad Payment Session State: " . $result["sessionState"]);
			}

			GopayHelper::checkPaymentStatus(
			    $paymentStatus,
			    $result["sessionState"],
			    (float)$targetGoId,
			    $orderNumber,
			    (int)$totalPriceInCents,
			    $currency,
			    $productName,
			    $secureKey);

			return $result;

        } catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			throw new Exception("Communication with WS failed");
        }
    }
    
	/**
	 * Zruseni predautorizovani plateb
	 *
	 * @param float $paymentSessionId - identifikator platby
	 * @param float $targetGoId - identifikator prijemnce - GoId
	 * @param string $secureKey - kryptovaci klic prideleny GoPay
	 */
	public function voidAuthorization($paymentSessionId,
		$targetGoId,
		$secureKey) {

		try {

			//inicializace WS
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			$sessionEncryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentSession((float)$targetGoId,
						(float)$paymentSessionId,
						$secureKey)),
				$secureKey);

			$paymentSession =  array(
				"targetGoId" => (float)$targetGoId,
				"paymentSessionId" => (float)$paymentSessionId,
				"encryptedSignature" => $sessionEncryptedSignature);

			$paymentResult = $go_client->__call('voidAuthorization', array('sessionInfo'=>$paymentSession));

			if ($paymentResult->result == GopayHelper::CALL_RESULT_FAILED) {
				throw new Exception("voided autorization failed [" . $paymentResult->resultDescription . "]");

			} else if ($paymentResult->result == GopayHelper::CALL_RESULT_ACCEPTED) {
				//zruseni predautorizace platby bylo zarazeno ke zpracovani

				throw new Exception(GopayHelper::CALL_RESULT_ACCEPTED);
			}

			//Overeni podpisu
			GopayHelper::checkPaymentResult($paymentResult->paymentSessionId,
				$paymentResult->encryptedSignature,
				$paymentResult->result,
				$paymentSessionId,
				$secureKey);

		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			throw new Exception("SOAP error");
		}

	}

	/**
	 * Zruseni opakovani plateb
	 *
	 * @param float $paymentSessionId - identifikator platby
	 * @param float $targetGoId - identifikator prijemnce - GoId
	 * @param string $secureKey - kryptovaci klic prideleny GoPay
	 */
	public function voidRecurrentPayment($paymentSessionId,
		$targetGoId,
		$secureKey) {

		try {

			//inicializace WS
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			$hash = GopayHelper::hash(
				GopayHelper::concatPaymentSession((float)$targetGoId,
					(float)$paymentSessionId,
					$secureKey));

			$sessionEncryptedSignature = GopayHelper::encrypt($hash, $secureKey);

			$paymentSession = array(
				"targetGoId" => (float)$targetGoId,
				"paymentSessionId" => (float)$paymentSessionId,
				"encryptedSignature" => $sessionEncryptedSignature);

			$paymentResult = $go_client->__call('voidRecurrentPayment', array('sessionInfo'=>$paymentSession));

			if ($paymentResult->result == GopayHelper::CALL_RESULT_FAILED) {
				throw new Exception("void recurrency failed [" . $paymentResult->resultDescription . "]");

			} else if ($paymentResult->result == GopayHelper::CALL_RESULT_ACCEPTED) {
				//zruseni opakovani platby bylo zarazeno ke zpracovani

				throw new Exception(GopayHelper::CALL_RESULT_ACCEPTED);

			}

			//Overeni podpisu
			GopayHelper::checkPaymentResult($paymentResult->paymentSessionId,
			    $paymentResult->encryptedSignature,
			    $paymentResult->result,
			    $paymentSessionId,
			    $secureKey);

		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			throw new Exception("SOAP error");
		}

	}

	/**
	 * Založení opakovane platby
	 *
	 * @param float  $parentPaymentSessionId - identifikator rodicovske platby
	 * @param int	 $recurrentPaymentOrderNumber - identifikator objednavky
	 * @param int    $recurrentPaymentTotalPriceInCents - castka
	 * @param string $recurrentPaymentCurrency - mena (CZK)
	 * @param string $recurrentPaymentProductName - popis objednavky
	 * @param float  $targetGoId - identifikator prijemnce - GoId
	 * @param string $secureKey - kryptovaci klic prideleny GoPay
	 */
	public function performRecurrence($parentPaymentSessionId,
		$recurrentPaymentOrderNumber,
		$recurrentPaymentTotalPriceInCents,
		$recurrentPaymentCurrency,
		$recurrentPaymentProductName,
		$targetGoId,
		$secureKey) {
		try {

			//inicializace WS
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			$encryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatRecurrenceRequest(
						(float)$parentPaymentSessionId,
						(int)$recurrentPaymentOrderNumber,
						(int)$recurrentPaymentTotalPriceInCents,
						(float)$targetGoId,
						$secureKey)),
				$secureKey);

			$recurrenceRequest =  array(
				"parentPaymentSessionId" => (float)$parentPaymentSessionId,
				"orderNumber" => (int)$recurrentPaymentOrderNumber,
				"totalPrice" => (int)$recurrentPaymentTotalPriceInCents,
				"targetGoId" => (float)$targetGoId,
				"encryptedSignature" => $encryptedSignature);

			$status = $go_client->__call('createRecurrentPayment', array('recurrenceRequest' => $recurrenceRequest));

			if ($status->result == GopayHelper::CALL_COMPLETED) {

				GopayHelper::checkPaymentStatus($status,
					GopayHelper::CREATED,
					(float)$targetGoId,
					(int)$recurrentPaymentOrderNumber,
					(int)$recurrentPaymentTotalPriceInCents,
					$recurrentPaymentCurrency,
					$recurrentPaymentProductName,
					$secureKey);

				return $status->paymentSessionId;

			} else {
				throw new Exception("Bad payment status");

			}

		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			throw new Exception("SOAP error");
		}

	}

	/**
	 * Dokončení platby
	 *
	 * @param float $paymentSessionId - identifikator platby
	 * @param float $targetGoId - identifikator prijemnce - GoId
	 * @param string $secureKey - kryptovaci klic prideleny GoPay
	 */
	public function capturePayment($paymentSessionId,
		$targetGoId,
		$secureKey) {
		try {

			//inicializace WS
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			$sessionEncryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentSession((float)$targetGoId,
						(float)$paymentSessionId,
						$secureKey)),
				$secureKey);

			$paymentSession =  array(
				"targetGoId" => (float)$targetGoId,
				"paymentSessionId" => (float)$paymentSessionId,
				"encryptedSignature" => $sessionEncryptedSignature);

			$paymentResult = $go_client->__call('capturePayment', array('sessionInfo'=>$paymentSession));


			if ($paymentResult->result == GopayHelper::CALL_RESULT_FAILED) {
				throw new Exception("payment not captured [" . $paymentResult->resultDescription . "]");

			} else if ($paymentResult->result == GopayHelper::CALL_RESULT_ACCEPTED) {
				// dokonceni platby bylo zarazeno ke zpracovani

				throw new Exception(GopayHelper::CALL_RESULT_ACCEPTED);

			}

			return $paymentResult->paymentSessionId;

		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			throw new Exception("SOAP error");
		}
	}

	/**
	 * Vraceni platby
	 *
	 * @param float $paymentSessionId - identifikator platby
	 * @param float $targetGoId - identifikator prijemnce - GoId
	 * @param string $secureKey - kryptovaci klic prideleny GoPay
	 */
	public function refundPayment($paymentSessionId,
		$targetGoId,
		$secureKey) {
		try {

			//inicializace WS
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			$sessionEncryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentSession((float)$targetGoId,
						(float)$paymentSessionId,
						$secureKey)),
				$secureKey);

			$paymentSession =  array(
				"targetGoId" => (float)$targetGoId,
				"paymentSessionId" => (float)$paymentSessionId,
				"encryptedSignature" => $sessionEncryptedSignature);

			$paymentResult = $go_client->__call('refundPayment', array('sessionInfo'=>$paymentSession));

			if ($paymentResult->result == GopayHelper::CALL_RESULT_FAILED) {

				throw new Exception("payment not refunded [" . $paymentResult->resultDescription . "]");

			} else if ($paymentResult->result == GopayHelper::CALL_RESULT_ACCEPTED) {
				//vraceni platby bylo zarazeno ke zpracovani

				throw new Exception(GopayHelper::CALL_RESULT_ACCEPTED);

			}

			return $paymentResult->paymentSessionId;

		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			throw new Exception("SOAP error");
		}
	}

	/**
	 * Castecne vraceni platby
	 *
	 * @param float $paymentSessionId - identifikator platby
	 * @param float $amount - castka na vraceni
	 * @param String $currency - mena
	 * @param String $description - popis vraceni platby
	 * @param float $targetGoId - identifikator prijemnce - GoId
	 * @param string $secureKey - kryptovaci klic prideleny GoPay
	 */
	public function refundPaymentPartially($paymentSessionId,
		$amount,
		$currency,
		$description,
		$targetGoId,
		$secureKey) {
		try {

			//inicializace WS
			ini_set("soap.wsdl_cache_enabled","0");
			$go_client = new SoapClient(GopayConfig::ws(), array());

			$sessionEncryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatRefundRequest((float)$targetGoId,
						(float)$paymentSessionId,
						$amount,
						$currency,
						$description,
						$secureKey)),
				$secureKey);

			$refundRequest =  array(
				"targetGoId" => (float)$targetGoId,
				"paymentSessionId" => (float)$paymentSessionId,
				"amount" => $amount,
				"currency" => $currency,
				"description" => $description,
				"encryptedSignature" => $sessionEncryptedSignature);

			$paymentResult = $go_client->__call("partiallyRefundPayment", array("refundRequest"=>$refundRequest));

			if ($paymentResult->result == GopayHelper::CALL_RESULT_FAILED) {
				throw new Exception("payment not refunded [" . $paymentResult->resultDescription . "]");

			} else if ($paymentResult->result == GopayHelper::CALL_RESULT_ACCEPTED) {
				//vraceni platby bylo zarazeno ke zpracovani

				throw new Exception(GopayHelper::CALL_RESULT_ACCEPTED);

			}

			return $paymentResult->paymentSessionId;

		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
			throw new Exception("SOAP error");
		}
	}

}
