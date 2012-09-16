<?php

namespace Markette\Gopay\Api;

use \SoapClient;
use \SoapFault;



/**
 * Predpokladem je PHP verze 5.1.2 a vyssi. Pro volání WS je pouzit modul soap.
 * 
 * Obsahuje funkcionality pro vytvoreni platby a kontrolu stavu platby prostrednictvim WS. 
 */
class GopaySoap {

//  ---		ESHOP   ----

	/**
	 * Vytvoreni platby pomoci WS z eshopu
	 * 
	 * @param long $eshopGoId - identifikator eshopu - GoId
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param int $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $variableSymbol - identifikator objednavky v eshopu
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene eshopu
	 * @param string $paymentChannels - platebni kanaly, ktere se zobrazi na plat. brane
	 * 
	 * @return paymentSessionId
	 * @return -1 vytvoreni platby neprobehlo uspesne
	 * @return -2 chyba komunikace WS
	 */
	public static function createEshopPayment(
  		$eshopGoId,
  		$productName,
  		$totalPriceInCents,
  		$variableSymbol,
  		$successURL,
  		$failedURL,
  		$secret,
  		$paymentChannels
  	) {

		try {

			ini_set("soap.wsdl_cache_enabled","0");
	  		$go_client = new SoapClient(GopayConfig::ws(), array());

			/*
			 * Sestaveni pozadavku pro zalozeni platby
			 */
			$encryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentCommand(
						(float)$eshopGoId,
						$productName, 
						(int)$totalPriceInCents,
						$variableSymbol,
						$failedURL,
						$successURL,
						$secret)
				),
				$secret);

			$payment_command = array(
		               "eshopGoId" => (float)$eshopGoId,
		               "productName" => trim($productName),
		               "totalPrice" => (int)$totalPriceInCents,
		               "variableSymbol" => trim($variableSymbol),
					   "successURL" => trim($successURL),
		               "failedURL" => trim($failedURL),
		               "encryptedSignature" => $encryptedSignature,
		               "paymentChannels" => join($paymentChannels, ",")
		     );

		 	/*
		 	 * Vytvareni platby na strane GoPay prostrednictvim WS 
		 	 */
			$payment_status = $go_client->__call('createPaymentSession', array('paymentCommand'=>$payment_command));

		 	/*
		 	 * Kontrola stavu platby - musi byt ve stavu WAITING, kontrola parametru platby
		 	 */
 			if (GopayHelper::checkEshopPaymentResult($payment_status, 
 										'WAITING',
 										(float)$eshopGoId,
		 								$variableSymbol,
		 								(int)$totalPriceInCents,
		 								$productName,
		 								$secret)
		 	) {
		 								
 				return $payment_status->paymentSessionId;	 				

 			} else { 				
  				/*
 				 * Chyba pri vytvareni platby
 				 */
 				return -1;

 			}

		} catch (SoapFault $f) {
			/*
			 * Chyba pri komunikaci s WS
			 */
			return -2;
		}
	}
	
	/**
	 * Vytvoreni platby s udaji o zakaznikovi pomoci WS z eshopu
	 * 
	 * @param long $eshopGoId - identifikator eshopu - GoId
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param int $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $variableSymbol - identifikator objednavky v eshopu
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene eshopu
	 * @param string $paymentChannels - platebni kanaly, ktere se zobrazi na plat. brane
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
	 * @return paymentSessionId
	 * @return -1 vytvoreni platby neprobehlo uspesne
	 * @return -2 chyba komunikace WS
	 */
	public static function createCustomerEshopPayment(
  		$eshopGoId,
  		$productName,
  		$totalPriceInCents,
  		$variableSymbol,
  		$successURL,
  		$failedURL,
  		$secret,
  		$paymentChannels,
		$firstName,
		$lastName,
		$city,
		$street,
		$postalCode,
		$countryCode,
		$email,
		$phoneNumber
  	) {

		try {

			ini_set("soap.wsdl_cache_enabled","0");
	  		$go_client = new SoapClient(GopayConfig::ws(), array());
	  		
	  		/*
	  		 * Sestaveni pozadavku pro zalozeni platby 
	  		 */
			$encryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentCommand(
						(float)$eshopGoId,
						$productName, 
						(int)$totalPriceInCents,
						$variableSymbol,
						$failedURL,
						$successURL,
						$secret)
				),
				$secret);
			
			$customerData = array(
					"firstName" => $firstName,
					"lastName" => $lastName,
					"city" => $city,
					"street" => $street,
					"postalCode" => $postalCode,
					"countryCode" => $countryCode,
					"email" => $email,
					"phoneNumber" => $phoneNumber
			);

			$customerPaymentCommand = array(
		               "eshopGoId" => (float)$eshopGoId,
		               "productName" => trim($productName),
		               "totalPrice" => (int)$totalPriceInCents,
		               "variableSymbol" => trim($variableSymbol),
					   "successURL" => trim($successURL),
		               "failedURL" => trim($failedURL),
		               "encryptedSignature" => $encryptedSignature,
		               "customerData" => $customerData,
		               "paymentChannels" => join($paymentChannels, ",")
		     );

		 	/*
		 	 * Vytvareni platby na strane GoPay prostrednictvim WS 
		 	 */
			$payment_status = $go_client->__call('createCustomerPaymentSession', array('paymentCommand'=>$customerPaymentCommand));

			/*
			 * Kontrola stavu platby - musi byt ve stavu WAITING, kontrola parametru platby 
			 */
 			if (GopayHelper::checkEshopPaymentResult($payment_status, 
 										'WAITING',
 										(float)$eshopGoId,
		 								$variableSymbol,
		 								(int)$totalPriceInCents,
		 								$productName,
		 								$secret)
		 	) {

 				return $payment_status->paymentSessionId;

 			} else { 				
 				/*
 				 * Chyba pri vytvareni platby
 				 */
 				return -1;
 			}

		} catch (SoapFault $f) {
			/*
			 * Chyba pri komunikaci s WS
			 */
			return -2;			
		}		
	}

	/**
	 * Kontrola stavu platby eshopu
	 * - verifikace parametru z redirectu
	 * - kontrola stavu platby
	 *
	 * @param long $paymentSessionId - identifikator platby 
	 * @param long $eshopGoId - identifikator eshopu - GoId
	 * @param string $variableSymbol - identifikator objednavky v eshopu
	 * @param int $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param string $secret - kryptovaci heslo pridelene eshopu
	 * 	  
	 * @return $result
	 *  result["code"] 		  - kod vysledku volani
	 *  result["description"] - popis vysledku volani
	 */
	public static function isEshopPaymentDone(
		$paymentSessionId,
		$eshopGoId,
		$variableSymbol,
		$totalPriceInCents,
		$productName,
		$secret
	) {
	 	$result = array();

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
												GopayHelper::concatPaymentSession((float)$eshopGoId,
																				(float)$paymentSessionId, 
																				$secret)), 
																				$secret);			

			$payment_session =  array(
		               "eshopGoId" => (float)$eshopGoId,
		               "paymentSessionId" => (float)$paymentSessionId,
		               "encryptedSignature" => $sessionEncryptedSignature
		     );

		 	/*
		 	 * Kontrola stavu platby na strane GoPay prostrednictvim WS 
		 	 */
		 	$payment_status = $go_client->__call('paymentStatusGW2', array('paymentSessionInfo'=>$payment_session));
		 	
		 	$result["description"] = $payment_status->resultDescription; 
	 		$result["code"] = $payment_status->sessionState;

		 	/*
		 	 * Kontrola zaplacenosti objednavky, verifikace parametru objednavky
		 	 */
		 	if (
		 		($result["code"] == GopayHelper::PAYMENT_DONE
		 				&& ! GopayHelper::checkEshopPaymentStatus(
		 								$payment_status, 
		 								'PAYMENT_DONE',
		 								(float)$eshopGoId,
		 								$variableSymbol,
		 								(int)$totalPriceInCents,
		 								$productName,
		 								$secret)
		 		)
		 		||
		 		($result["code"] == GopayHelper::WAITING
				 		&& ! GopayHelper::checkEshopPaymentStatus(
									$payment_status, 
									'WAITING',
									(float)$eshopGoId,
									$variableSymbol,
									(int)$totalPriceInCents,
									$productName,
									$secret)
				)
				||
				($result["code"] == GopayHelper::TIMEOUTED
				 		&& ! GopayHelper::checkEshopPaymentStatus(
									$payment_status, 
									'TIMEOUTED',
									(float)$eshopGoId,
									$variableSymbol,
									(int)$totalPriceInCents,
									$productName,
									$secret)
				)
				||
				($result["code"] == GopayHelper::CANCELED
				 		&& ! GopayHelper::checkEshopPaymentStatus(
									$payment_status, 
									'CANCELED',
									(float)$eshopGoId,
									$variableSymbol,
									(int)$totalPriceInCents,
									$productName,
									$secret)
				)
		 	) {
		 		/*
		 		 * Platba nesprobehla korektne
		 		 */
		 		$result["code"] = GopayHelper::FAILED;

		 	}
		 			 			
		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
		 	$result["code"] = GopayHelper::FAILED; 
		 	$result["description"] = GopayHelper::FAILED; 
			
		}
		
		return $result;
	}
	
//  ---      UZIVATEL   ----
	

	/**
	 * Vytvoreni platby uzivatele pomoci WS
	 * 
	 * @param long $buyerGoId - identifikator uzivatele - GoId
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param int $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $variableSymbol - identifikator objednavky
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene uzivateli
	 * 
	 * @return paymentSessionId
	 * @return -1 vytvoreni platby neprobehlo uspesne
	 * @return -2 chyba komunikace WS
	 */
	public static function createBuyerPayment(
  		$buyerGoId,
  		$productName,
  		$totalPriceInCents,
  		$variableSymbol,
  		$successURL,
  		$failedURL,
  		$secret
  	) {
 
		try {

			ini_set("soap.wsdl_cache_enabled","0");
	  		$go_client = new SoapClient(GopayConfig::ws(), array());
	  		
			/* 
			 * Sestaveni pozadavku pro zalozeni platby
			 */
			$encryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentCommand(
						(float)$buyerGoId,
						$productName, 
						(int)$totalPriceInCents,
						$variableSymbol,
						$failedURL,
						$successURL,
						$secret)
				),
				$secret);

			$payment_command = array(
		               "buyerGoId" => (float)$buyerGoId,
		               "productName" => trim($productName),
		               "totalPrice" => (int)$totalPriceInCents,
		               "variableSymbol" => trim($variableSymbol),
					   "successURL" => trim($successURL),
		               "failedURL" => trim($failedURL),
		               "encryptedSignature" => $encryptedSignature
		     );

            /*
             * Vytvareni platby na strane GoPay prostrednictvim WS 
             */
		 	$payment_status = $go_client->__call('createPaymentSession', array('paymentCommand'=>$payment_command));

		 	/*
		 	 * Kontrola stavu platby - musi byt ve stavu WAITING, kontrola parametru platby
		 	 */
 			if (GopayHelper::checkBuyerPaymentResult($payment_status, 
 										'WAITING',
 										(float)$buyerGoId,
		 								$variableSymbol,
		 								(int)$totalPriceInCents,
		 								$productName,
		 								$secret)
		 	) {

 				return $payment_status->paymentSessionId;

 			} else {
  				/*
 				 * Chyba pri vytvareni platby
 				 */
 				return -1;

 			}

		} catch (SoapFault $f) {
			/*
			 * Chyba pri komunikaci s WS
			 */
			return -2;
		}
	}

	/**
	 * Kontrola provedeni platby uzivatele
	 * - verifikace parametru z redirectu
	 * - kontrola provedeni platby
	 * 	  
	 * @param long $paymentSessionId - identifikator platby 
	 * @param long $eshopGoId - identifikator uzivatele - GoId
	 * @param string $variableSymbol - identifikator objednavky
	 * @param int $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param string $secret - kryptovaci heslo pridelene uzivateli
	 * 	  
	 * @return $result
	 *  result["code"] 		  - kod vysledku volani
	 *  result["description"] - popis vysledku volani
	 */
	public static function isBuyerPaymentDone(
		$paymentSessionId,
		$buyerGoId,
		$variableSymbol,
		$totalPriceInCents,
		$productName,
		$secret
	) {
	 	$result = array();

		try {

			//inicializace WS
			ini_set("soap.wsdl_cache_enabled","0");
	  		$go_client = new SoapClient(GopayConfig::ws(), array());

			//sestaveni dotazu na stav platby
			$sessionEncryptedSignature=GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentSession(
							(float)$buyerGoId,
							(float)$paymentSessionId, 
							$secret)
					), $secret);			

			$payment_session =  array(
		               "buyerGoId" => (float)$buyerGoId,
		               "paymentSessionId" => (float)$paymentSessionId,
		               "encryptedSignature" => $sessionEncryptedSignature
		     );

            /*
             * Kontrola stavu platby na strane GoPay prostrednictvim WS 
             */
		 	$payment_status = $go_client->__call('paymentStatusGW2', array('paymentSessionInfo'=>$payment_session));
		 	
		 	$result["description"] = $payment_status->resultDescription; 
	 		$result["code"] = $payment_status->sessionState;

		 	/*
		 	 * Kontrola zaplacenosti objednavky, verifikace parametru objednavky
		 	 */
			if (! GopayHelper::checkBuyerPaymentStatus($payment_status, 
					'PAYMENT_DONE', 
					(float)$buyerGoId,
					$variableSymbol,
					(int)$totalPriceInCents,
					$productName,
					$secret)
			) {
		 		/*
		 		 * Platba neprobehla korektne
		 		 */
				$result["code"] = GopayHelper::FAILED;
		 	}
		 			 			
		} catch (SoapFault $f) {
			/*
			 * Chyba v komunikaci s GoPay serverem
			 */
		 	$result["code"] = GopayHelper::FAILED; 
		 	$result["description"] = GopayHelper::FAILED; 
			
		}
		
		return $result;
	}

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

}
