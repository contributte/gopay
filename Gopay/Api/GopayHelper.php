<?php

namespace Markette\Gopay\Api;


/**
 * Předpokladem je PHP verze 5.1.2 a vyšší s modulem mcrypt.
 *
 * Pomocna trida pro platbu v systemu GoPay
 * 
 * - sestavovani retezcu pro podpis komunikacnich elementu
 * - sifrovani/desifrovani retezcu
 * - verifikace podpisu informacnich retezcu
 */
class GopayHelper {

	public static function getResultMessage($result) {

		$resultMesages = array(
			"PAYMENT_DONE" => "Platba byla úspěšně provedena.<br>Děkujeme Vám za využití našich služeb.",
			"CANCELED" => "Platba byla zrušena.<br>Opakujte platbu znovu, prosím.",
			"TIMEOUTED" => "Platba byla zrušena.<br>Opakujte platbu znovu, prosím.",
			"WAITING" => "Platba zatím nebyla provedena. O provedení platby Vás budeme neprodleně informovat pomocí emailu s potvrzením platby. Pokud neobdržíte do následujícího pracovního dne potvrzovací email o platbě, kontaktujte podporu GoPay na emailu podpora@gopay.cz.",
			"WAITING_OFFLINE" => "Platba zatím nebyla provedena. Na platební bráně GoPay jste získali platební údaje a na Váš email Vám byly zaslány informace k provedení platby. O provedení platby Vás budeme budeme neprodleně informovat pomocí emailu s potvrzením platby.",
			"FAILED" => "V průběhu platby nastala chyba. Kontaktujte podporu GoPay na emailu podpora@gopay.cz."
		);
		
		return isset($resultMesages[$result]) ? $resultMesages[$result] : "";

	}

	const iconRychloplatba = "https://www.gopay.cz/download/PT_rychloplatba.png";		

	const iconDaruj = "https://www.gopay.cz/download/PT_daruj.png";		

	const iconBuynow = "https://www.gopay.cz/download/PT_buynow.png";		

	const iconDonate = "https://www.gopay.cz/download/PT_donate.png";		
	/*
	 * Kody stavu platby 
	 */
	const PAYMENT_DONE = "PAYMENT_DONE";
	const CANCELED = "CANCELED";
	const TIMEOUTED = "TIMEOUTED";
	const WAITING = "WAITING";
	const FAILED = "FAILED";

	/**
	 * Sestaveni retezce pro podpis platebniho prikazu.
	 *
	 * @param float $goId - identifikator eshopu / uzivateli prideleny GoPay
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param float $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $variableSymbol - identifikator objednavky v eshopu
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
	 * @return retezec pro podpis - platebni kanaly, ktere se zobrazi na plat. brane
	 */
	public static function concatPaymentCommand(
  		$goId,
  		$productName, 
  		$totalPriceInCents, 
  		$variableSymbol,
  		$failedURL,
  		$successURL, 
  		$secret) {

        return $goId."|".trim($productName)."|".$totalPriceInCents."|".trim($variableSymbol)."|".trim($failedURL)."|".trim($successURL)."|".$secret; 
  	}

  	/**
  	 * Sestaveni retezce pro podpis vysledku vytvoreni platby
  	 *
	 * @param float $goId - identifikator eshopu / uzivateli prideleny GoPay
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param float $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $variableSymbol - identifikator objednavky v eshopu
  	 * @param string $result - vysledek volani (CALL_COMPLETED / CALL_FAILED)
  	 * @param string $sessionState - stav platby (PAYMENT_DONE, WAITING, TIMEOUTED, CANCELED)
	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
  	 * @return retezec pro podpis
  	 */
	public static function concatPaymentResult(
  		$goId,
  		$productName, 
  		$totalPriceInCents, 
  		$variableSymbol,
  		$result,
  		$sessionState,
  		$secret) {

        return $goId."|".trim($productName)."|".$totalPriceInCents."|".trim($variableSymbol)."|".$result."|".$sessionState."|".$secret; 
  	}

  	 /**
  	 * Sestaveni retezce pro podpis vysledku stav platby.
  	 *
	 * @param float $goId - identifikator eshopu / uzivateli prideleny GoPay
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param float $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $variableSymbol - identifikator objednavky v eshopu
  	 * @param string $result - vysledek volani (CALL_COMPLETED / CALL_FAILED)
  	 * @param string $sessionState - stav platby (PAYMENT_DONE, WAITING, TIMEOUTED, CANCELED)
  	 * @param string $paymentChannel - pouzita platebni metoda
	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
  	 * @return retezec pro podpis
  	 */
	public static function concatPaymentStatus(
  		$goId,
  		$productName, 
  		$totalPriceInCents, 
  		$variableSymbol,
  		$result,
  		$sessionState,
  		$paymentChannel,
  		$secret) {

        return $goId."|".trim($productName)."|".$totalPriceInCents."|".trim($variableSymbol)."|".$result."|".$sessionState."|".$paymentChannel."|".$secret; 
  	}
  	
  	
  	/**
  	 * Sestaveni retezce pro podpis platební session pro přesměrování na platební bránu GoPay 
  	 * nebo volání GoPay služby stav platby
  	 *
  	 * @param float $goId - identifikator eshopu / uzivateli prideleny GoPay
  	 * @param float $paymentSessionId - identifikator platby na GoPay
  	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
  	 * @return retezec pro podpis
  	 */
	public static function concatPaymentSession(
  		$goId,
  	 	$paymentSessionId,  	 	 
  	 	$secret) {
        return $goId."|".$paymentSessionId."|".$secret; 
  	}

  	/**
  	 * Sestaveni retezce pro podpis parametru platby (paymentIdentity)
  	 *
  	 * @param float $goId - identifikator eshopu / uzivateli prideleny GoPay
  	 * @param float $paymentSessionId - identifikator platby na GoPay
  	 * @param string $variableSymbol - identifikator platby na eshopu
  	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
  	 * @return retezec pro podpis
  	 */
	public static function concatPaymentIdentity(
  		$goId,
  	 	$paymentSessionId,
  	 	$variableSymbol, 
  	 	$secret) {
        return $goId."|".$paymentSessionId."|".trim($variableSymbol)."|".$secret; 
  	}

  	/**
  	 * Sestaveni retezce pro podpis vytvoreni uzivatele
  	 *
  	 * @param float $goId - identifikator eshopu / uzivateli prideleny GoPay
  	 * @param float $buyerUsername - uzivatelske jmeno uzivatele
  	 * @param string $buyerEmail - email uzivatele
  	 * @param string $secret - kryptovaci heslo pridelene uzivateli, urcene k podepisovani komunikace
  	 * @return retezec pro podpis
  	 */
	public static function concatBuyer(
  		$goId,
  	 	$buyerUsername,
  	 	$buyerEmail, 
  	 	$secret) {
        return $goId."|".trim($buyerUsername)."|".trim($buyerEmail)."|".$secret; 
  	}

  	/**
  	 * Sifrovani dat 3DES
  	 *
  	 * @param string $data - retezec, ktery se sifruje
  	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
  	 * @return sifrovany obsah v HEX forme
  	 */
	public static function encrypt($data, $secret) {
  		$td = mcrypt_module_open (MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        $mcrypt_iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init ($td, substr($secret, 0, mcrypt_enc_get_key_size($td)), $mcrypt_iv);
        $encrypted_data = mcrypt_generic ($td, $data);
        mcrypt_generic_deinit ($td);
        mcrypt_module_close ($td);

        return bin2hex($encrypted_data);
  	}

  	/**
  	 * desifrovani
  	 *
  	 * @param string $data - zasifrovana data
  	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
  	 * @return desifrovany retezec
  	 */
	public static function decrypt($data, $secret) {
  		$td = mcrypt_module_open (MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        $mcrypt_iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init ($td, substr($secret, 0, mcrypt_enc_get_key_size($td)), $mcrypt_iv);

        $decrypted_data = mdecrypt_generic($td, GopayHelper::convert($data));
        mcrypt_generic_deinit ($td);
        mcrypt_module_close ($td);

		return Trim($decrypted_data);

  	}  	

  	/**
  	 * hash SHA1 dat
  	 *
  	 * @param string $data - data k hashovani
  	 * @return otisk dat SHA1
  	 */
	public static function hash($data) {
  		if (function_exists("sha1")==true) {
  			$hash = sha1($data, true);

  		} else {
  			$hash = mhash(MHASH_SHA1,$data);
  		}

  		return bin2hex($hash);   		
  	}

  	/**
  	 * konverze z HEX -> string
  	 *
  	 * @param string $hexString - data k konverzi
  	 * @return konverze z HEX -> string
  	 */
	public static function convert($hexString) {

  		$hexLenght = strlen($hexString);
  		// only hex numbers is allowed                
  		if ($hexLenght % 2 != 0 || preg_match("/[^\da-fA-F]/",$hexString)) return FALSE;
  		$binString = "";
  		for ($x = 1; $x <= $hexLenght/2; $x++)                
  		{                        
  			$binString .= chr(hexdec(substr($hexString,2 * $x - 2,2)));

  		}

  		return $binString;

  	}

	/**
	 * Kontrola vysledku vytvoreni platby proti internim udajum objednavky - verifikace podpisu.
	 *
	 * @param mixed $payment_result - vysledek volani createPayment
	 * @param string $session_state - ocekavany stav paymentSession (WAITING, PAYMENT_DONE)
	 * @param float $eshopGoId - identifikace eshopu - GoId eshopu pridelene GoPay
	 * @param string $variableSymbol - identifikace akt. objednavky na eshopu
	 * @param float $totalPriceInCents - cena objednavky v halerich
	 * @param string $productName - nazev objedavky / zbozi
	 * @param string $secret - kryptovaci heslo pridelene eshopu, urcene k podepisovani komunikace
	 * 
	 * @return true
	 * @return false
	 */
	public static function checkEshopPaymentResult(
  				$payment_result,
  	 			$session_state,
  	 			$eshopGoId,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 			
  	 			) {

		$valid = true;
		
		/*
		 * Kontrola parametru objednavky
		 */
		$valid = GopayHelper::checkPaymentResultCommon(
  				$payment_result,
  	 			$session_state,
  	 			null,
  	 			$eshopGoId,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 					
		);

		if ( $valid ) {

			/*
			 * Kontrola podpisu objednavky
			 */
			$hashedSignature = GopayHelper::hash(
					GopayHelper::concatPaymentResult(
						$payment_result->eshopGoId,
						$payment_result->productName,						
						$payment_result->totalPrice,
						$payment_result->variableSymbol,
						$payment_result->result,
						$payment_result->sessionState,
						$secret)
			);			

			$decryptedHash = GopayHelper::decrypt($payment_result->encryptedSignature,$secret);

			if ($decryptedHash != $hashedSignature) {
				$valid = false;
//				echo "PS invalid signature <br>";
			}
		}

		return $valid;
	}

	/**
	 * Kontrola vysledku vytvoreni platby proti internim udajum objednavky - verifikace podpisu.
	 *
	 * @param mixed $payment_result - vysledek volani createPayment
	 * @param string $session_state - ocekavany stav paymentSession (WAITING, PAYMENT_DONE)
	 * @param float $buyerGoId - identifikace uzivatele - GoId uzivatele pridelene GoPay
	 * @param string $variableSymbol - identifikace akt. objednavky
	 * @param float $totalPriceInCents - cena objednavky v halerich
	 * @param string $productName - nazev objednavky / zbozi
	 * @param string $secret - kryptovaci heslo pridelene uzivateli, urcene k podepisovani komunikace
	 * 
	 * @return true
	 * @return false
	 */
	public static function checkBuyerPaymentResult(
  				$payment_result,
  	 			$session_state,
  	 			$buyerGoId,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 			
  	 			) {

		$valid = true;
		
		/*
		 * Kontrola parametru objednavky
		 */
		$valid = GopayHelper::checkPaymentResultCommon(
  				$payment_result,
  	 			$session_state,
  	 			$buyerGoId,
  	 			null,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 					
		);

		if ( $valid ) {

			/*
			 * Kontrola podpisu objednavky
			 */
			$hashedSignature=GopayHelper::hash(
					GopayHelper::concatPaymentResult(
						$payment_result->buyerGoId,
						$payment_result->productName,						
						$payment_result->totalPrice,
						$payment_result->variableSymbol,
						$payment_result->result,
						$payment_result->sessionState,
						$secret)
			);			

			$decryptedHash = GopayHelper::decrypt($payment_result->encryptedSignature,$secret);

			if ($decryptedHash != $hashedSignature) {
				$valid = false;
//				echo "PS invalid signature <br>";
			}
		}

		return $valid;
	}
	
	/**
	 * Kontrola stavu platby proti internim udajum objednavky - verifikace podpisu.
	 *
	 * @param mixed $payment_status - vysledek volani paymentStatus
	 * @param string $session_state - ocekavany stav paymentSession (WAITING, PAYMENT_DONE)
	 * @param float $eshopGoId - identifikace eshopu - GoId eshopu pridelene GoPay
	 * @param string $variableSymbol - identifikace akt. objednavky na eshopu
	 * @param float $totalPriceInCents - cena objednavky v halerich
	 * @param string $productName - nazev objednavky / zbozi
	 * @param string $secret - kryptovaci heslo pridelene eshopu, urcene k podepisovani komunikace
	 * 
	 * @return true
	 * @return false
	 */
	public static function checkEshopPaymentStatus(
  				$payment_status,
  	 			$session_state,
  	 			$eshopGoId,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 			
  	 			) {

		$valid = true;
		
		$valid = GopayHelper::checkPaymentResultCommon(
  				$payment_status,
  	 			$session_state,
  	 			null,
  	 			$eshopGoId,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 					
		);
		

		if ( $valid) {
			/*
			 * Kontrola podpisu objednavky
			 */
			$hashedSignature=GopayHelper::hash(
					GopayHelper::concatPaymentStatus(
						$payment_status->eshopGoId,
						$payment_status->productName,						
						$payment_status->totalPrice,
						$payment_status->variableSymbol,
						$payment_status->result,
						$payment_status->sessionState,
						$payment_status->paymentChannel,
						$secret)
			);			

			$decryptedHash = GopayHelper::decrypt($payment_status->encryptedSignature,$secret);

			if ($decryptedHash != $hashedSignature) {
				$valid = false;
//				echo "PS invalid signature <br>";
			}
		}

		return $valid;
	}
	
	/**
	 * Kontrola stavu platby proti internim udajum objednavky uzivatele - verifikace podpisu
	 *
	 * @param mixed $payment_status - vysledek volani paymentStatus
	 * @param string $session_state - ocekavany stav paymentSession (WAITING, PAYMENT_DONE)
	 * @param float $buyerGoId - identifikace uzivatele - GoId uzivatele pridelene GoPay
	 * @param string $variableSymbol - identifikace akt. objednavky
	 * @param float $totalPriceInCents - cena objednavky v halerich
	 * @param string $productName - nazev objednavky / zbozi
	 * @param string $secret - kryptovaci heslo pridelene uzivateli, urcene k podepisovani komunikace
	 * 
	 * @return true
	 * @return false
	 */
	public static function checkBuyerPaymentStatus(
  				$payment_status,
  	 			$session_state,
  	 			$buyerGoId,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 			
  	 			) {

		$valid = true;
		
		/*
		 * Kontrola parametru objednavky
		 */
		$valid = GopayHelper::checkPaymentResultCommon(
  				$payment_status,
  	 			$session_state,
  	 			$buyerGoId,
  	 			null,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 					
		);

		if ( $valid) {
		
			/*
			 * Kontrola parametru objednavky
			 */
			$hashedSignature=GopayHelper::hash(
					GopayHelper::concatPaymentStatus(
						$payment_status->buyerGoId,
						$payment_status->productName,						
						$payment_status->totalPrice,
						$payment_status->variableSymbol,
						$payment_status->result,
						$payment_status->sessionState,
						$payment_status->paymentChannel,
						$secret)
			);			

			$decryptedHash = GopayHelper::decrypt($payment_status->encryptedSignature,$secret);

			if ($decryptedHash != $hashedSignature) {
				$valid = false;
//				echo "PS invalid signature <br>";
			}
		}

		return $valid;
	}
	
	
	/**
	 * Kontrola parametru platby proti internim udajum objednavky uzivatele
	 *
	 * @param string $payment_result - vysledek volani paymentStatus
	 * @param string $session_state - ocekavany stav paymentSession (WAITING, PAYMENT_DONE)
	 * @param float $buyerGoId - identifikace uzivatele - GoId eshopu / uzivatele pridelene GoPay
	 * @param string $variableSymbol - identifikace akt. objednavky
	 * @param float $totalPriceInCents - cena objednavky v halerich
	 * @param string $productName - nazev zbozi / zbozi
	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
	 * 
	 * @return true
	 * @return false
	 */
	private static function checkPaymentResultCommon(
  				$payment_result,
  	 			$session_state,
  	 			$buyerGoId,
  	 			$eshopGoId,
  	 			$variableSymbol,  	 			
  	 			$totalPriceInCents,
  	 			$productName,
  	 			$secret	 			
  	 			) {

		$valid = true;

		if ( $payment_result) {

			if ( $payment_result->result != 'CALL_COMPLETED') {
				$valid = false;
//				echo "PS invalid call state state<br>";
			}

			if ( $payment_result->sessionState != $session_state) {
				$valid = false;
//				echo "PS invalid session state<br>";			
			}

			if (trim($payment_result->variableSymbol) != trim($variableSymbol)) {
				$valid = false;
//				echo "PS invalid VS <br>";
			}

			if (trim($payment_result->productName) != trim($productName)) {
				$valid = false;
//				echo "PS invalid PN <br>";
			}

			if ($payment_result->eshopGoId != $eshopGoId) {
				$valid = false;
//				echo "PS invalid EID<br>";
			}

			if ($payment_result->buyerGoId != $buyerGoId) {
				$valid = false;
//				echo "PS invalid EID<br>";
			}

			if ($payment_result->totalPrice != $totalPriceInCents) {
				$valid = false;
//				echo "PS invalid price<br>";
			}

		} else {
			$valid = false;
//			echo "none payment status <br>";
		}

		return $valid;
	}
	
	
	/**
	 * Kontrola parametru predavanych ve zpetnem volani po potvrzeni/zruseni platby - verifikace podpisu.
	 *
	 * @param float $returnedGoId - goId vracene v redirectu
	 * @param float $returnedPaymentSessionId - paymentSessionId vracene v redirectu
	 * @param string $returnedVariableSymbol - variableSymbol vraceny v redirectu - identifikator platby na eshopu
	 * @param string $returnedEncryptedSignature - kontrolni podpis vraceny v redirectu 
	 * @param float $goId - identifikace eshopu - GoId eshopu pridelene GoPay
	 * @param string $variableSymbol - identifikace akt. objednavky
	 * @param string $secret - kryptovaci heslo pridelene eshopu / uzivateli, urcene k podepisovani komunikace
	 * 
	 * @return true
	 * @return false
	 */
	public static function checkPaymentIdentity(
  				$returnedGoId,
  				$returnedPaymentSessionId,  				
  				$returnedVariableSymbol,
  				$returnedEncryptedSignature,
  	 			$goId,
  	 			$variableSymbol,  	 			
  	 			$secret  	 			
  	 			) {

		$valid = true;
		if (trim($returnedVariableSymbol) != trim($variableSymbol)) {
			$valid = false;
//			echo "PI invalid VS <br>";
		}

		if ($returnedGoId != $goId) {
			$valid = false;
//			echo "PI invalid EID<br>";
		}

		$hashedSignature=GopayHelper::hash(
				GopayHelper::concatPaymentIdentity(
					(float)$returnedGoId,
					(float)$returnedPaymentSessionId,						
					$returnedVariableSymbol,
					$secret)
			);
		$decryptedHash = GopayHelper::decrypt($returnedEncryptedSignature, $secret);

		if ($decryptedHash != $hashedSignature) {
			$valid = false;
//			echo "PI invalid signature <br>";
		}

		return $valid;
	}


	/**
	 * Kontrola parametru predavanych ve zpetnem volani po vytvoreni uzivatele - verifikace podpisu.
	 *
	 * @param mixed $create_result - vysledek volani createBuyer
	 * @param float $goId - identifikace uzivatele - GoId uzivatele pridelene GoPay
	 * @param string $buyerUsername - uzivatelske jmeno uzivatele
	 * @param string $buyerEmail - email uzivatele
	 * @param string $secret - kryptovaci heslo pridelene uzivateli, urcene k podepisovani komunikace
	 * 
	 * @return true
	 * @return false
	 */
	public static function checkCreateBuyerResult(
  				$create_result,
  	 			$goId,
  	 			$buyerUsername,  	 			
  	 			$buyerEmail,
  	 			$secret	 			
  	 			) {

		$valid = true;

		if ( $create_result) {

			if ( $create_result->buyerGoId == "") {
				$valid = false;
//				echo "PS invalid buyerGoId<br>";
			}

			if ( $create_result->buyerUsername == "") {
				$valid = false;
//				echo "PS invalid buyerUsername<br>";
			}

			if ( $create_result->result != 'CALL_COMPLETED') {
				$valid = false;
//				echo "PS invalid call state state<br>";
			}

			if ( $create_result->resultDescription != 'BUYER_CREATED') {
				$valid = false;
//				echo "PS invalid call state description<br>";
			}

			if ( $valid ) {
	
				$hashedSignature=GopayHelper::hash(
						GopayHelper::concatBuyer(
								(float)$goId,
								$buyerUsername, 
								$buyerEmail,
								$secret)
				);			
	
				$decryptedHash = GopayHelper::decrypt($create_result->encryptedSignature,$secret);
	
				if ($decryptedHash != $hashedSignature) {
					$valid = false;
//					echo "PS invalid signature <br>";
				}
			}
		} else {
			$valid = false;
//			echo "No create result <br>";
			
		}

		return $valid;
	}


//  ---      ESHOP   ----
	
  	/**
	 * Sestaveni formulare platebniho tlacitka pro jednoduchou integraci
	 * 
	 *
	 * @param float $eshopGoId - identifikace eshopu - GoId eshopu pridelene GoPay
	 * @param int $totalPrice - cena objednavky v halerich
	 * @param string $productName - nazev objednavky / zbozi
	 * @param string $variableSymbol - identifikace akt. objednavky na eshopu
	 * @param string $successURL - URL, kam se ma prejit po uspesnem zaplaceni
	 * @param string $failedURL - URL, kam se ma prejit po neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene eshopu, urcene k podepisovani komunikace
	 * @param array $paymentChannels - pole plat. metod, ktere se zobrazi na brane
	 * 
	 * @return HTML kod platebniho formulare
	 */
	public static function createEshopForm(
  			$eshopGoId,
  			$totalPrice,
  			$productName,
  			$variableSymbol,
  			$successURL,
  			$failedURL,
  	 		$secret,
  	 		$paymentChannels
  	 		) {

  		$encryptedSignature = GopayHelper::encrypt(
  				GopayHelper::hash(
  						GopayHelper::concatPaymentCommand(
  								(float)$eshopGoId,
		  						$productName, 
		  						(int)$totalPrice,
		  						$variableSymbol,
		  						$failedURL,	
		  						$successURL,
		  						$secret)
		  					), $secret);

  		$ouput = "";
  		$ouput .= "<form method='post' action='" . GopayConfig::baseIntegrationURL() . "'>\n";
		$ouput .= '<input type="hidden" name="paymentCommand.eshopGoId" value="' . $eshopGoId . '" />' . "\n";
		$ouput .= '<input type="hidden" name="paymentCommand.productName" value="' . trim($productName) . '" />' . "\n";
		$ouput .= '<input type="hidden" name="paymentCommand.totalPrice" value="' . $totalPrice . '" />' . "\n";
		$ouput .= '<input type="hidden" name="paymentCommand.variableSymbol" value="' . trim($variableSymbol) . '"/>'."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.successURL" value="'. trim($successURL) . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.failedURL" value="'. trim($failedURL) . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.paymentChannels" value="'. join($paymentChannels,",") . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.encryptedSignature" value="' . $encryptedSignature . '" />' ."\n";
		$ouput .= '<input type="submit" name="buy" value="Zaplatit" class="button">' ."\n";
		$ouput .= "</form>\n";

	  	return $ouput;
  	}
 	

  	/**
	 * Sestaveni platebniho tlacitka formou odkazu pro jednoduchou integraci
	 * 
	 *
	 * @param float $eshopGoId - identifikace eshopu - GoId eshopu pridelene GoPay
	 * @param int $totalPrice - cena objednavky v halerich
	 * @param string $productName - nazev objednavky / zbozi
	 * @param string $variableSymbol - identifikace akt. objednavky na eshopu
	 * @param string $successURL - URL, kam se ma prejit po uspesnem zaplaceni
	 * @param string $failedURL - URL, kam se ma prejit po neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene eshopu, urcene k podepisovani komunikace
	 * @param array $paymentChannels - pole plat. metod, ktere se zobrazi na brane
	 * 
	 * @return HTML kod platebniho tlacitka
	 */
	public static function createEshopHref(
  			$eshopGoId,
  			$totalPrice,
  			$productName,
  			$variableSymbol,
  			$successURL,
  			$failedURL,
  	 		$secret,
  	 		$paymentChannels
  	 		) {

  		$encryptedSignature = GopayHelper::encrypt(
  				GopayHelper::hash(
  						GopayHelper::concatPaymentCommand(
  								(float)$eshopGoId,
		  						$productName, 
		  						(int)$totalPrice,
		  						$variableSymbol,
		  						$failedURL,
		  						$successURL,
		  						$secret)
		  					), $secret);

  		$params = "";
		$params .= "paymentCommand.eshopGoId=" . $eshopGoId;
  		$params .= "&paymentCommand.productName=" . urlencode($productName);
  		$params .= "&paymentCommand.totalPrice=" . $totalPrice;
  		$params .= "&paymentCommand.variableSymbol=" . urlencode($variableSymbol);
  		$params .= "&paymentCommand.successURL=" . urlencode($successURL);
  		$params .= "&paymentCommand.failedURL=" . urlencode($failedURL);
  		$params .= "&paymentCommand.paymentChannels=" . join($paymentChannels,",");
  		$params .= "&paymentCommand.encryptedSignature=" . urlencode($encryptedSignature);

  		$ouput = "";
  		$ouput .= "<a target='_blank' href='" . GopayConfig::baseIntegrationURL() . "?" . $params . "'>";
  		$ouput .= " Zaplatit ";
  		$ouput .= "</a>";

	  	return $ouput;
  	}
	
  	/**
	 * Sestaveni formulare platebniho tlacitka s udaji o zakaznikovi pro jednoduchou integraci
	 * 
	 * @param float $eshopGoId - identifikace eshopu - GoId eshopu pridelene GoPay
	 * @param int $totalPrice - cena objednavky v halerich
	 * @param string $productName - nazev objednavky / zbozi
	 * @param string $variableSymbol - identifikace akt. objednavky na eshopu
	 * @param string $successURL - URL, kam se ma prejit po uspesnem zaplaceni
	 * @param string $failedURL - URL, kam se ma prejit po neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene eshopu, urcene k podepisovani komunikace
	 * @param array $paymentChannels - pole plat. metod, ktere se zobrazi na brane
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
	 * @return HTML kod platebniho formulare
	 */
	public static function createEshopFormWithCustomer(
  			$eshopGoId,
  			$totalPrice,
  			$productName,
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

  		$encryptedSignature = GopayHelper::encrypt(
  				GopayHelper::hash(
  						GopayHelper::concatPaymentCommand(
  								(float)$eshopGoId,
		  						$productName, 
		  						(int)$totalPrice,
		  						$variableSymbol,
		  						$failedURL,	
		  						$successURL,
		  						$secret)
		  					), $secret);

  		$ouput = "";
  		$ouput .= "<form method='post' action='" . GopayConfig::baseIntegrationURL() . "'>\n";
		$ouput .= '<input type="hidden" name="paymentCommand.eshopGoId" value="' . $eshopGoId . '" />' . "\n";
		$ouput .= '<input type="hidden" name="paymentCommand.productName" value="' . $productName . '" />' . "\n";
		$ouput .= '<input type="hidden" name="paymentCommand.totalPrice" value="' . $totalPrice . '" />' . "\n";
		$ouput .= '<input type="hidden" name="paymentCommand.variableSymbol" value="' . $variableSymbol . '"/>'."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.successURL" value="'. $successURL . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.failedURL" value="'. $failedURL . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.paymentChannels" value="'. join($paymentChannels,",") . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.encryptedSignature" value="' . $encryptedSignature . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.customerData.firstName" value="' . $firstName . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.customerData.lastName" value="' . $lastName . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.customerData.city" value="' . $city . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.customerData.street" value="' . $street . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.customerData.postalCode" value="' . $postalCode . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.customerData.countryCode" value="' . $countryCode . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.customerData.email" value="' . $email . '" />' ."\n";
		$ouput .= '<input type="hidden" name="paymentCommand.customerData.phoneNumber" value="' . $phoneNumber . '" />' ."\n";
		$ouput .= '<input type="submit" name="buy" value="Zaplatit" class="button">' ."\n";
		$ouput .= "</form>\n";

	  	return $ouput;
  	}
 	

  	/**
	 * Sestaveni platebniho tlacitka formou odkazu s udaji o zakaznikovi pro jednoduchou integraci
	 *
	 * @param float $eshopGoId - identifikace eshopu - GoId eshopu pridelene GoPay
	 * @param int $totalPrice - cena objednavky v halerich
	 * @param string $productName - nazev objednavky / zbozi
	 * @param string $variableSymbol - identifikace akt. objednavky na eshopu
	 * @param string $successURL - URL, kam se ma prejit po uspesnem zaplaceni
	 * @param string $failedURL - URL, kam se ma prejit po neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene eshopu, urcene k podepisovani komunikace
	 * @param array $paymentChannels - pole plat. metod, ktere se zobrazi na brane
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
	 * @return HTML kod platebniho tlacitka
	 */
	public static function createEshopHrefWithCustomer(
  			$eshopGoId,
  			$totalPrice,
  			$productName,
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

  		$encryptedSignature = GopayHelper::encrypt(
  				GopayHelper::hash(
  						GopayHelper::concatPaymentCommand(
  								(float)$eshopGoId,
		  						$productName, 
		  						(int)$totalPrice,
		  						$variableSymbol,
		  						$failedURL,
		  						$successURL,
		  						$secret)
		  					), $secret);

  		$params = "";
		$params .= "paymentCommand.eshopGoId=" . $eshopGoId;
  		$params .= "&paymentCommand.productName=" . urlencode($productName);
  		$params .= "&paymentCommand.totalPrice=" . $totalPrice;
  		$params .= "&paymentCommand.variableSymbol=" . urlencode($variableSymbol);
  		$params .= "&paymentCommand.successURL=" . urlencode($successURL);
  		$params .= "&paymentCommand.failedURL=" . urlencode($failedURL);
  		$params .= "&paymentCommand.paymentChannels=" . join($paymentChannels,",");
  		$params .= "&paymentCommand.encryptedSignature=" . urlencode($encryptedSignature);
  		$params .= "&paymentCommand.customerData.firstName=" . urlencode($firstName);
  		$params .= "&paymentCommand.customerData.lastName=" . urlencode($lastName);
  		$params .= "&paymentCommand.customerData.city=" . urlencode($city);
  		$params .= "&paymentCommand.customerData.street=" . urlencode($street);
  		$params .= "&paymentCommand.customerData.postalCode=" . urlencode($postalCode);
  		$params .= "&paymentCommand.customerData.countryCode=" . urlencode($countryCode);
  		$params .= "&paymentCommand.customerData.email=" . urlencode($email);
  		$params .= "&paymentCommand.customerData.phoneNumber=" . urlencode($phoneNumber);

  		$ouput = "";
  		$ouput .= "<a target='_blank' href='" . GopayConfig::baseIntegrationURL() . "?" . $params . "'>";
  		$ouput .= " Zaplatit ";
  		$ouput .= "</a>";

	  	return $ouput;
  	}

//  ---      UZIVATEL   ----
	
  	/**
	 * Sestaveni formulare platebniho tlacitka
	 *
	 * @param float $buyerGoId - identifikace uzivatele - GoId uzivatele pridelene GoPay
	 * @param int $totalPrice - cena objednavky v halerich
	 * @param string $productName - nazev objednvky / zbozi
	 * @param string $variableSymbol - identifikace akt. objednavky
	 * @param string $successURL - URL, kam se ma prejit po uspesnem zaplaceni
	 * @param string $failedURL - URL, kam se ma prejit po neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene uzivateli, urcene k podepisovani komunikace
	 * @param string $iconUrl - URL ikony tlacitka - viz konstanty tridy 
	 * 
	 * @return HTML kod platebniho formulare
	 */
	public static function createBuyerForm(
  			$buyerGoId,
  			$totalPrice,
  			$productName,
  			$variableSymbol,
  			$successURL,
  			$failedURL,
  	 		$secret,
  	 		$iconUrl
  	 		) {

  		$encryptedSignature = GopayHelper::encrypt(
  				GopayHelper::hash(
  						GopayHelper::concatPaymentCommand(
  								(float)$buyerGoId,
		  						$productName, 
		  						(int)$totalPrice,
		  						$variableSymbol,
		  						$failedURL,
		  						$successURL,
		  						$secret)
		  					), $secret);

  		$formBuffer = "";
  		$formBuffer .= "<form method='post' action='" . GopayConfig::buyerBaseIntegrationURL() . "' target='_blank'>\n";
  		$formBuffer .= "<input type='hidden' name='paymentCommand.buyerGoId' value='" . $buyerGoId . "' />\n";
  		$formBuffer .= "<input type='hidden' name='paymentCommand.totalPrice' value='" . $totalPrice . "' />\n";
  		$formBuffer .= "<input type='hidden' name='paymentCommand.productName' value='" . $productName . "' />\n";
  		$formBuffer .= "<input type='hidden' name='paymentCommand.variableSymbol' value='" . $variableSymbol . "' />\n";
  		$formBuffer .= "<input type='hidden' name='paymentCommand.successURL' value='" . $successURL . "' />\n";
  		$formBuffer .= "<input type='hidden' name='paymentCommand.failedURL' value='" . $failedURL . "' />\n";
  		$formBuffer .= "<input type='hidden' name='paymentCommand.encryptedSignature' value='" . $encryptedSignature . "' />\n";

  		if (empty($iconUrl)) {
  			$formBuffer .= "<input type='submit' name='submit' value='Zaplatit' class='button'>\n";
  		} else {
  			$formBuffer .= "<input type='submit' name='submit' value='' style='background:url(" . $iconUrl . ") no-repeat;width:100px;height:30px;border:none;'>\n";
  		}

  		$formBuffer .= "</form>\n";

	  	return $formBuffer;
  	}

  	/**
	 * Sestaveni platebniho tlacitka jako odkazu
	 * 
	 *
	 * @param float $buyerGoId - identifikace uzivatele - GoId uzivatele pridelene GoPay
	 * @param int $totalPrice - cena objednavky v halerich
	 * @param string $productName - nazev objednvky / zbozi
	 * @param string $variableSymbol - identifikace akt. objednavky
	 * @param string $successURL - URL, kam se ma prejit po uspesnem zaplaceni
	 * @param string $failedURL - URL, kam se ma prejit po neuspesnem zaplaceni
	 * @param string $secret - kryptovaci heslo pridelene uzivateli, urcene k podepisovani komunikace
	 * @param string $iconUrl - URL ikony tlacitka - viz konstanty tridy 
	 * 
	 * @return HTML kod platebniho tlacitka
	 */
	public static function createBuyerHref(
  			$buyerGoId,
  			$totalPrice,
  			$productName,
  			$variableSymbol,
  			$successURL,
  			$failedURL,
  	 		$secret,
  	 		$iconUrl
  	 		) {

  		$encryptedSignature = GopayHelper::encrypt(
  				GopayHelper::hash(
  						GopayHelper::concatPaymentCommand(
  								(float)$buyerGoId,
		  						$productName, 
		  						(int)$totalPrice,
		  						$variableSymbol,
		  						$failedURL,
		  						$successURL,
		  						$secret)
		  					), $secret);

  		$params = "";
		$params .= "paymentCommand.buyerGoId=" . $buyerGoId;
  		$params .= "&paymentCommand.productName=" . urlencode($productName);
  		$params .= "&paymentCommand.totalPrice=" . $totalPrice;
  		$params .= "&paymentCommand.variableSymbol=" . urlencode($variableSymbol);
  		$params .= "&paymentCommand.successURL=" . urlencode($successURL);
  		$params .= "&paymentCommand.failedURL=" . urlencode($failedURL);
  		$params .= "&paymentCommand.encryptedSignature=" . urlencode($encryptedSignature);

  		$formBuffer = "";
  		$formBuffer .= "<a target='_blank' href='" . GopayConfig::buyerBaseIntegrationURL() . "?" . $params . "' >";

  		if (empty($iconUrl)) {
  			$formBuffer .= " Zaplatit ";
  		} else {
  			$formBuffer .= " <img src='" . $iconUrl . "' border='0' style='border:none;'/> ";
  		}

  		$formBuffer .= "</a>";

	  	return $formBuffer;
  	}

}
