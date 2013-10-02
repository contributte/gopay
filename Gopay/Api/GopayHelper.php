<?php

namespace Markette\Gopay\Api;

use Exception;


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
	/**
	 * Kody stavu platby
	 */
	const CREATED = "CREATED";
	const PAYMENT_METHOD_CHOSEN = "PAYMENT_METHOD_CHOSEN";
	const PAID = "PAID";
	const AUTHORIZED = "AUTHORIZED";
	const CANCELED = "CANCELED";
	const TIMEOUTED = "TIMEOUTED";
	const REFUNDED = "REFUNDED";
	const PARTIALLY_REFUNDED = "PARTIALLY_REFUNDED";
	const FAILED = "FAILED";

	const CALL_COMPLETED = "CALL_COMPLETED";
	const CALL_FAILED = "CALL_FAILED";


	/**
	 * Konstanty pro opakovanou platbu
	 */
	const RECURRENCE_CYCLE_MONTH = "MONTH";
	const RECURRENCE_CYCLE_WEEK = "WEEK";
	const RECURRENCE_CYCLE_DAY = "DAY";
	const RECURRENCE_CYCLE_ON_DEMAND = "ON_DEMAND";

	/**
	 * Konstanty pro zruseni opakovani platby
	 */
	const CALL_RESULT_ACCEPTED = "ACCEPTED";
	const CALL_RESULT_FINISHED = "FINISHED";
	const CALL_RESULT_FAILED = "FAILED";

	/**
	 * URL obrazku tlacitek pro platebni formulare a odkazy
	 */
	const iconRychloplatba = "https://www.gopay.cz/download/PT_rychloplatba.png";
	const iconDaruj = "https://www.gopay.cz/download/PT_daruj.png";
	const iconBuynow = "https://www.gopay.cz/download/PT_buynow.png";
	const iconDonate = "https://www.gopay.cz/download/PT_donate.png";

	/**
	 * Hlaseni o stavu platby
	 */
	const PAID_MESSAGE = "Platba byla úspěšně provedena.<br>Děkujeme Vám za využití našich služeb.";
	const CANCELED_MESSAGE = "Platba byla zrušena.<br>Opakujte platbu znovu, prosím.";
	const AUTHORIZED_MESSAGE = "Platba byla autorizována, čeká se na dokončení. O provedení platby Vás budeme budeme neprodleně informovat pomocí emailu s potvrzením platby.";
	const REFUNDED_MESSAGE = "Platba byla vrácena.";
	const PAYMENT_METHOD_CHOSEN_ONLINE_MESSAGE = "Platba zatím nebyla provedena. O provedení platby Vás budeme neprodleně informovat pomocí emailu s potvrzením platby. Pokud neobdržíte do následujícího pracovního dne potvrzovací email o platbě, kontaktujte podporu GoPay na emailu podpora@gopay.cz.";
	const PAYMENT_METHOD_CHOSEN_OFFLINE_MESSAGE = "Platba zatím nebyla provedena. Na platební bráně GoPay jste získali platební údaje a na Váš email Vám byly zaslány informace k provedení platby. O provedení platby Vás budeme budeme neprodleně informovat pomocí emailu s potvrzením platby.";
	const PAYMENT_METHOD_CHOSEN_MESSAGE = "Platba zatím nebyla provedena. O provedení platby Vás budeme neprodleně informovat pomocí emailu s potvrzením platby.";
	const FAILED_MESSAGE = "V průběhu platby nastala chyba. Kontaktujte podporu GoPay na emailu podpora@gopay.cz.";


	/**
	 * Ziskani korektniho hlaseni o stavu platby - po volani (GopaySoap::isPaymentDone)
	 *
	 * @param String $sessionState - stav platby. Hodnoty viz konstanty GopayHelper
	 * @param String $sessionSubState - detailnejsi popis stavu platby
	 *
	 * @return String retezec popisujici stav platby
	 */
	public static function getResultMessage($sessionState, $sessionSubState) {

		$result = "";

		if ($sessionState == GopayHelper::PAID) {
			$result = GopayHelper::PAID_MESSAGE;

		} else if ($sessionState == GopayHelper::CANCELED
			|| $sessionState == GopayHelper::TIMEOUTED
			|| $sessionState == GopayHelper::CREATED) {
			$result = GopayHelper::CANCELED_MESSAGE;

		} else if ($sessionState == GopayHelper::AUTHORIZED) {
			$result = GopayHelper::AUTHORIZED_MESSAGE;

		} else if ($sessionState == GopayHelper::REFUNDED) {
			$result = GopayHelper::REFUNDED_MESSAGE;

		} else if ($sessionState == GopayHelper::PAYMENT_METHOD_CHOSEN) {
			if (! empty($sessionSubState) && $sessionSubState == 101) {
				$result = GopayHelper::PAYMENT_METHOD_CHOSEN_ONLINE_MESSAGE;

			} else if (! empty($sessionSubState) && $sessionSubState == 102) {
				$result = GopayHelper::PAYMENT_METHOD_CHOSEN_OFFLINE_MESSAGE;

			} else {
				$result = GopayHelper::PAYMENT_METHOD_CHOSEN_MESSAGE;

			}

		} else {
			$result = GopayHelper::FAILED_MESSAGE;
		}

		return $result;
	}

	/**
	 * Sestaveni retezce pro podpis platebniho prikazu.
	 *
	 * @param float $goId - identifikator prijemce prideleny GoPay
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param float $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $currency - identifikator meny platby
	 * @param string $orderNumber - identifikator objednavky u prijemce
	 * @param string $failedURL - URL stranky, kam je zakaznik presmerovan po zruseni platby / neuspesnem zaplaceni
	 * @param string $successURL - URL stranky, kam je zakaznik presmerovan po uspesnem zaplaceni
	 * @param int $preAuthorization - jedna-li se o predautorizovanou platbu true => 1, false => 0, null=>""
	 * @param int $recurrentPayment - jedna-li se o opakovanou platbu true => 1, false => 0, null=>""
	 * @param date $recurrenceDateTo - do kdy se ma opakovana platba provadet
	 * @param string $recurrenceCycle - frekvence opakovane platby - mesic/tyden/den
	 * @param int $recurrencePeriod - pocet jednotek opakovani ($recurrencePeriod=3 ~ opakování jednou za tři jednotky (mesic/tyden/den))
	 * @param string $paymentChannels - platebni kanaly
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 * @return retezec pro podpis
	 */
	public static function concatPaymentCommand($goId,
												$productName,
												$totalPriceInCents,
												$currency,
												$orderNumber,
												$failedURL,
												$successURL,
												$preAuthorization,
												$recurrentPayment,
												$recurrenceDateTo,
												$recurrenceCycle,
												$recurrencePeriod,
												$paymentChannels,
												$secureKey) {

		$preAuthorization = GopayHelper::castBooleanForWS($preAuthorization);
		$recurrentPayment = GopayHelper::castBooleanForWS($recurrentPayment);

		return $goId . "|" . trim($productName) . "|" . $totalPriceInCents . "|" . trim($currency) . "|" . trim($orderNumber) . "|" . trim($failedURL) . "|" . trim($successURL) . "|" . $preAuthorization . "|" . $recurrentPayment . "|" . trim($recurrenceDateTo)."|".trim($recurrenceCycle) . "|" . trim($recurrencePeriod) . "|" . trim($paymentChannels) . "|" . $secureKey;
	}

	 /**
	 * Sestaveni retezce pro podpis vysledku stavu platby.
	 *
	 * @param float $goId - identifikator prijemce prideleny GoPay
	 * @param string $productName - popis objednavky zobrazujici se na platebni brane
	 * @param float $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $currency - identifikator meny platby
	 * @param string $orderNumber - identifikator objednavky u prijemce
	 * @param float $parentPaymentSessionId - id puvodni platby pri opakovane platbe
	 * @param int $preAuthorization - jedna-li se o predautorizovanou platbu true => 1, false => 0, null=>""
	 * @param int $recurrentPayment - jedna-li se o opakovanou platbu true => 1, false => 0, null=>""
	 * @param string $result - vysledek volani (CALL_COMPLETED / CALL_FAILED)
	 * @param string $sessionState - stav platby - viz GopayHelper
	 * @param string $sessionSubState - podstav platby - detailnejsi popis stavu platby
	 * @param string $paymentChannel - pouzita platebni metoda
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 * @return retezec pro podpis
	 */
	public static function concatPaymentStatus($goId,
												$productName,
												$totalPriceInCents,
												$currency,
												$orderNumber,
												$recurrentPayment,
												$parentPaymentSessionId,
												$preAuthorization,
												$result,
												$sessionState,
												$sessionSubState,
												$paymentChannel,
												$secureKey) {

		$preAuthorization = GopayHelper::castBooleanForWS($preAuthorization);
		$recurrentPayment = GopayHelper::castBooleanForWS($recurrentPayment);

		return $goId . "|" . trim($productName) . "|" . $totalPriceInCents . "|" . $currency . "|" . trim($orderNumber) . "|" . $recurrentPayment . "|" . $parentPaymentSessionId . "|" . $preAuthorization . "|" . $result . "|" . $sessionState . "|" . $sessionSubState . "|" . $paymentChannel . "|" . $secureKey;
	}


	/**
	 * Sestaveni retezce pro podpis platební session pro přesměrování na platební bránu GoPay
	 * nebo volání GoPay služby stav platby
	 *
	 * @param float $goId - identifikator prijemce prideleny GoPay
	 * @param float $paymentSessionId - identifikator platby na GoPay
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 * @return retezec pro podpis
	 */
	public static function concatPaymentSession($goId, $paymentSessionId, $secureKey) {

		return $goId . "|" . $paymentSessionId . "|" . $secureKey;
	}

	/**
	 * Sestaveni retezce pro podpis parametru platby (paymentIdentity)
	 *
	 * @param float $goId - identifikator prijemce prideleny GoPay
	 * @param float $paymentSessionId - identifikator platby na GoPay
	 * @param float $parentPaymentSessionId - id puvodni platby pri opakovane platbe
	 * @param string $orderNumber - identifikator platby u prijemce
	 * @param string $secureKey - kryptovaci heslo pridelene prijemci, urcene k podepisovani komunikace
	 * @return retezec pro podpis
	 */
	public static function concatPaymentIdentity($goId,
												$paymentSessionId,
												$parentPaymentSessionId,
												$orderNumber,
												$secureKey) {

		if ($parentPaymentSessionId == null) {
			$parentPaymentSessionId = "";
		}

		return $goId . "|" . $paymentSessionId . "|" . $parentPaymentSessionId . "|" . trim($orderNumber) . "|" . $secureKey;
	}

	/**
	 * Sestaveni retezce pro podpis.
	 *
	 * @param float $paymentSessionId - identifikator platby na GoPay
	 * @param string $result - vysledek volani
	 * @param string $secureKey - kryptovaci klic prideleny uzivateli, urceny k podepisovani komunikace
	 * @return retezec pro podpis
	 */
	public static function concatPaymentResult($paymentSessionId, $result, $secureKey) {

		return $paymentSessionId . "|" . trim($result) . "|" . $secureKey;
	}


	/**
	 * Sestaveni retezce pro stazeni vypisu plateb uzivatele
	 *
	 * @param date $dateFrom - datum (vcetne), od ktereho se generuje vypis
	 * @param date $dateTo - datum (vcetne), do ktereho se generuje vypis
	 * @param float $targetGoId - identifikator uzivatele prideleny GoPay
	 * @param string $currency - mena uctu, ze ktereho se vypis pohybu ziskava
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 * @return retezec pro podpis
	 */
	public static function concatStatementRequest($dateFrom,
												$dateTo,
												$targetGoId,
												$currency,
												$secureKey) {

		return $dateFrom . "|" . $dateTo . "|" . $targetGoId . "|" . $currency . "|" . $secureKey;
	}

	/**
	 * Sestaveni retezce pro podpis pozadavku opakovane platby.
	 *
	 * @param float $parentPaymentSessionId - id puvodni platby pri opakovane platbe
	 * @param float $targetGoId - identifikator prijemce prideleny GoPay
	 * @param string $orderNumber - identifikator platby u prijemce
	 * @param float $totalPriceInCents - celkova cena objednavky v halerich
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 * @return retezec pro podpis
	 */
	public static function concatRecurrenceRequest($parentPaymentSessionId,
												$orderNumber,
												$totalPriceInCents,
												$targetGoId,
												$secureKey) {

		return $parentPaymentSessionId . "|" . $targetGoId . "|" . $orderNumber . "|" . $totalPriceInCents . "|" . $secureKey;
	}

	/**
	* Sestaveni retezce pro podpis sessionInfo.
	*
	* @param float $targetGoId - identifikator prijemce prideleny GoPay
	* @param float $paymentSessionId - identifikator platby na GoPay
	* @param string $amount - castka na vraceni
	* @param string $currency - identifikator meny platby
	* @param string $description - popis refundace
	* @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	* @return retezec pro podpis
	*/
	public static function concatRefundRequest($targetGoId,
										   $paymentSessionId,
										   $amount,
										   $currency,
										   $description,
										   $secureKey) {

		return $targetGoId . "|" . $paymentSessionId . "|" . $amount . "|" . $currency . "|" . $description . "|" . $secureKey;
	}

	/**
	 * Sifrovani dat 3DES
	 *
	 * @param string $data - retezec, ktery se sifruje
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 * @return sifrovany obsah v HEX forme
	 */
	public static function encrypt($data, $secureKey) {
		$td = mcrypt_module_open (MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
		$mcrypt_iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init ($td, substr($secureKey, 0, mcrypt_enc_get_key_size($td)), $mcrypt_iv);
		$encrypted_data = mcrypt_generic ($td, $data);
		mcrypt_generic_deinit ($td);
		mcrypt_module_close ($td);

		return bin2hex($encrypted_data);
	}

	/**
	 * desifrovani
	 *
	 * @param string $data - zasifrovana data
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 * @return desifrovany retezec
	 */
	public static function decrypt($data, $secureKey) {
		$td = mcrypt_module_open (MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
		$mcrypt_iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init ($td, substr($secureKey, 0, mcrypt_enc_get_key_size($td)), $mcrypt_iv);

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

		$hexLength = strlen($hexString);

		// vstup musi byt HEX
		if ($hexLength % 2 != 0 || preg_match("/[^0-9a-fA-F]/", $hexString)) return FALSE;

		$binString = "";
		for ($x = 0; $x < $hexLength/2; $x++) {
			$binString .= chr(hexdec(substr($hexString, 2 * $x, 2)));

		}

		return $binString;
	}

	/**
	 * Kontrola stavu platby proti internim udajum objednavky - verifikace podpisu.
	 *
	 * @param mixed $paymentStatus - vysledek volani paymentStatus
	 * @param string $sessionState - ocekavany stav paymentSession (WAITING, PAYMENT_DONE)
	 * @param float $goId - identifikator prijemce prideleny GoPay
	 * @param string $orderNumber - identifikace akt. objednavky u prijemce
	 * @param float $totalPriceInCents - cena objednavky v halerich
	 * @param string $currency - identifikator meny platby
	 * @param string $productName - nazev objednavky / zbozi
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 *
	 * @throw Exception
	 */
	public static function checkPaymentStatus($paymentStatus,
											$sessionState,
											$goId,
											$orderNumber,
											$totalPriceInCents,
											$currency,
											$productName,
											$secureKey) {

		if (! empty($paymentStatus)) {

			if ( $paymentStatus->result != GopayHelper::CALL_COMPLETED) {
				throw new Exception("PS invalid call state state");
			}

			if ( $paymentStatus->sessionState != $sessionState) {
				throw new Exception("PS invalid session state");
			}

			if (trim($paymentStatus->orderNumber) != trim($orderNumber)) {
				throw new Exception("PS invalid VS");
			}

			if (trim($paymentStatus->productName) != trim($productName)) {
				throw new Exception("PS invalid PN");
			}

			if ($paymentStatus->targetGoId != $goId) {
				throw new Exception("PS invalid GoID");
			}

			if ($paymentStatus->totalPrice != $totalPriceInCents) {
				throw new Exception("PS invalid price");
			}

			if ($paymentStatus->currency != $currency) {
				throw new Exception("PS invalid currency");
			}

		} else {
			throw new Exception("None payment status");
		}
		/*
		 * Kontrola podpisu objednavky
		 */
		$hashedSignature = GopayHelper::hash(
				GopayHelper::concatPaymentStatus(
					$paymentStatus->targetGoId,
					$paymentStatus->productName,
					$paymentStatus->totalPrice,
					$paymentStatus->currency,
					$paymentStatus->orderNumber,
					$paymentStatus->recurrentPayment,
					$paymentStatus->parentPaymentSessionId,
					$paymentStatus->preAuthorization,
					$paymentStatus->result,
					$paymentStatus->sessionState,
					$paymentStatus->sessionSubState,
					$paymentStatus->paymentChannel,
					$secureKey)
		);

		$decryptedHash = GopayHelper::decrypt($paymentStatus->encryptedSignature, $secureKey);

		if ($decryptedHash != $hashedSignature) {
			throw new Exception("PS invalid signature");
		}
	}

	/**
	 * Kontrola parametru predavanych ve zpetnem volani po potvrzeni/zruseni platby - verifikace podpisu.
	 *
	 * @param float $returnedGoId - goId vracene v redirectu
	 * @param float $returnedPaymentSessionId - paymentSessionId vracene v redirectu
	 * @param float $returnedParentPaymentSessionId - id puvodni platby pri opakovane platbe
	 * @param string $returnedOrderNumber - identifikace objednavky vracena v redirectu - identifikator platby na eshopu
	 * @param string $returnedEncryptedSignature - kontrolni podpis vraceny v redirectu
	 * @param float $targetGoId - identifikace prijemce - GoId pridelene GoPay
	 * @param string $orderNumber - identifikace akt. objednavky
	 * @param string $secureKey - kryptovaci klic prideleny eshopu / uzivateli, urceny k podepisovani komunikace
	 *
	 * @throw Exception
	 */
	public static function checkPaymentIdentity($returnedGoId,
												$returnedPaymentSessionId,
												$returnedParentPaymentSessionId,
												$returnedOrderNumber,
												$returnedEncryptedSignature,
												$targetGoId,
												$orderNumber,
												$secureKey) {

		if (trim($returnedOrderNumber) != trim($orderNumber)) {
			throw new Exception("PI invalid VS");
		}

		if ($returnedGoId != $targetGoId) {
			throw new Exception("PI invalid GoID");
		}

		$hashedSignature=GopayHelper::hash(
				GopayHelper::concatPaymentIdentity(
					(float)$returnedGoId,
					(float)$returnedPaymentSessionId,
					(float)$returnedParentPaymentSessionId,
					$returnedOrderNumber,
					$secureKey));

		$decryptedHash = GopayHelper::decrypt($returnedEncryptedSignature, $secureKey);

		if ($decryptedHash != $hashedSignature) {
			throw new Exception("PS invalid signature");
		}
	}

	/**
	 * Kontrola parametru predavanych ve zpetnem volani po potvrzeni/zruseni platby - verifikace podpisu.
	 *
	 * @param float $returnedPaymentSessionId - paymentSessionId vracene v redirectu
	 * @param string $returnedEncryptedSignature - kontrolni podpis vraceny v redirectu
	 * @param float $paymentResult - vysledek volani
	 * @param float $paymentSessionId - identifikator platby na GoPay
	 * @param string $secureKey - kryptovaci klic prideleny eshopu / uzivateli, urceny k podepisovani komunikace
	 *
	 * @throw Exception
	 */
	public static function checkPaymentResult($returnedPaymentSessionId,
											$returnedEncryptedSignature,
											$paymentResult,
											$paymentSessionId,
											$secureKey) {

		if ($returnedPaymentSessionId != $paymentSessionId) {
			throw new Exception("PaymentResult invalid PSID");
		}

		$hashedSignature = GopayHelper::hash(
								GopayHelper::concatPaymentResult(
									(float)$paymentSessionId,
									$paymentResult,
									$secureKey));

		$decryptedHash = GopayHelper::decrypt($returnedEncryptedSignature, $secureKey);

		if ($decryptedHash != $hashedSignature) {
			throw new Exception("PaymentResult invalid signature");
		}
	}

	/**
	 * Sestaveni formulare platebniho tlacitka pro jednoduchou integraci
	 *
	 *
	 * @param float $targetGoId - identifikace prijemce - GoId pridelene GoPay
	 * @param string $productName - nazev objednavky / zbozi
	 * @param int $totalPrice - cena objednavky v halerich
	 * @param string $currency - identifikator meny
	 * @param string $orderNumber - identifikace objednavky u prijemce
	 * @param string $successURL - URL, kam se ma prejit po uspesnem zaplaceni
	 * @param string $failedURL - URL, kam se ma prejit po neuspesnem zaplaceni
	 * @param array $paymentChannels - pole plat. metod, ktere se zobrazi na brane
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 *
	 * @param boolean $preAuthorization - jedna-li se o predautorizovanou platbu
	 * @param boolean $recurrentPayment - jedna-li se o opakovanou platbu
	 * @param date $recurrenceDateTo - do kdy se ma opakovana platba provadet
	 * @param string $recurrenceCycle - frekvence opakovresultane platby - mesic/tyden/den
	 * @param int $recurrencePeriod - pocet plateb v cyklu $recurrenceCycle - kolikrat v mesici/... se opakovana platba provede
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
	 * @param string $p1 - $p4 - volitelne parametry, ketre budou po navratu z platebni brany predany e-shopu
	 *
	 * @param string $lang - jazyk platebni brany
	 *
	 * @return HTML kod platebniho formulare
	 */
	public static function createPaymentForm($targetGoId,
											$productName,
											$totalPrice,
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

		$paymentChannelsString = (!empty($paymentChannels)) ? join($paymentChannels, ",") : "";

		$encryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentCommand((float)$targetGoId,
													$productName,
													(int)$totalPrice,
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

		$ouput = "";
		$ouput .= "<form method='post' action='" . GopayConfig::baseIntegrationURL() . "'>\n";
			$ouput .= '<input type="hidden" name="paymentCommand.targetGoId" value="' . $targetGoId . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.productName" value="' . trim($productName) . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.totalPrice" value="' . $totalPrice . '" />' . "\n";
			$ouput .= '<input type="hidden" name="paymentCommand.currency" value="' . trim($currency) . '" />' . "\n";
			$ouput .= '<input type="hidden" name="paymentCommand.orderNumber" value="' . trim($orderNumber) . '"/>'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.successURL" value="' . trim($successURL) . '" />' ."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.failedURL" value="' . trim($failedURL) . '" />' ."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.paymentChannels" value="' . trim($paymentChannelsString) . '" />' ."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.defaultPaymentChannel" value="' . trim($defaultPaymentChannel) . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.encryptedSignature" value="' . $encryptedSignature . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.preAuthorization" value="' . GopayHelper::castBoolean2String($preAuthorization) . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.recurrentPayment" value="' . GopayHelper::castBoolean2String($recurrentPayment) . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.recurrenceDateTo" value="' . $recurrenceDateTo . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.recurrenceCycle" value="' . $recurrenceCycle . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.recurrencePeriod" value="' . $recurrencePeriod . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.customerData.firstName" value="' . $firstName . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.customerData.lastName" value="' . $lastName . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.customerData.city" value="' . $city . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.customerData.street" value="' . $street . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.customerData.postalCode" value="' . $postalCode . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.customerData.countryCode" value="' . $countryCode . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.customerData.email" value="' . $email . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.customerData.phoneNumber" value="' . $phoneNumber . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.p1" value="' . $p1 . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.p2" value="' . $p2 . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.p3" value="' . $p3 . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.p4" value="' . $p4 . '" />'."\n";
			$ouput .= '<input type="hidden" name="paymentCommand.lang" value="' . $lang . '" />'."\n";
		$ouput .= '<input type="submit" name="buy" value="Zaplatit" class="button">'."\n";
		$ouput .= "</form>\n";

		return $ouput;
	}


	/**
	 * Sestaveni platebniho tlacitka formou odkazu pro jednoduchou integraci
	 *
	 * @param float $targetGoId - identifikace prijemce - GoId pridelene GoPay
	 * @param string $productName - nazev objednavky / zbozi
	 * @param int $totalPrice - cena objednavky v halerich
	 * @param string $currency - identifikator meny
	 * @param string $orderNumber - identifikace objednavky u prijemce
	 * @param string $successURL - URL, kam se ma prejit po uspesnem zaplaceni
	 * @param string $failedURL - URL, kam se ma prejit po neuspesnem zaplaceni
	 * @param array $paymentChannels - pole plat. metod, ktere se zobrazi na brane
	 * @param string $secureKey - kryptovaci klic prideleny prijemci, urceny k podepisovani komunikace
	 *
	 * @param boolean $preAuthorization - jedna-li se o predautorizovanou platbu
	 * @param boolean $recurrentPayment - jedna-li se o opakovanou platbu
	 * @param date $recurrenceDateTo - do kdy se ma opakovana platba provadet
	 * @param string $recurrenceCycle - frekvence opakovresultane platby - mesic/tyden/den
	 * @param int $recurrencePeriod - pocet plateb v cyklu $recurrenceCycle - kolikrat v mesici/... se opakovana platba provede
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
	 * @param string $p1 - $p4 - volitelne parametry, ketre budou po navratu z platebni brany predany e-shopu
	 *
	 * @param string $lang - jazyk platebni brany
	 *
	 * @return HTML kod platebniho tlacitka
	 */
	public static function createPaymentHref($targetGoId,
											$productName,
											$totalPrice,
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

		$paymentChannelsString = (!empty($paymentChannels)) ? join($paymentChannels, ",") : "";

		$encryptedSignature = GopayHelper::encrypt(
				GopayHelper::hash(
					GopayHelper::concatPaymentCommand((float)$targetGoId,
													$productName,
													(int)$totalPrice,
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

		$params = "";
		$params .= "paymentCommand.targetGoId=" . $targetGoId;
		$params .= "&paymentCommand.productName=" . urlencode($productName);
		$params .= "&paymentCommand.totalPrice=" . $totalPrice;
		$params .= "&paymentCommand.currency=" . $currency;
		$params .= "&paymentCommand.orderNumber=" . urlencode($orderNumber);
		$params .= "&paymentCommand.successURL=" . urlencode($successURL);
		$params .= "&paymentCommand.failedURL=" . urlencode($failedURL);
		$params .= "&paymentCommand.paymentChannels=" . urlencode($paymentChannelsString);
		$params .= "&paymentCommand.defaultPaymentChannel=" . urlencode($defaultPaymentChannel);
		$params .= "&paymentCommand.encryptedSignature=" . urlencode($encryptedSignature);
		$params .= "&paymentCommand.preAuthorization=" . GopayHelper::castBoolean2String($preAuthorization);
		$params .= "&paymentCommand.recurrentPayment=" . GopayHelper::castBoolean2String($recurrentPayment);
		$params .= "&paymentCommand.recurrenceDateTo=" . urlencode($recurrenceDateTo);
		$params .= "&paymentCommand.recurrenceCycle=" . urlencode($recurrenceCycle);
		$params .= "&paymentCommand.recurrencePeriod=" . $recurrencePeriod;
		$params .= "&paymentCommand.customerData.firstName=" . urlencode($firstName);
		$params .= "&paymentCommand.customerData.lastName=" . urlencode($lastName);
		$params .= "&paymentCommand.customerData.city=" . urlencode($city);
		$params .= "&paymentCommand.customerData.street=" . urlencode($street);
		$params .= "&paymentCommand.customerData.postalCode=" . urlencode($postalCode);
		$params .= "&paymentCommand.customerData.countryCode=" . urlencode($countryCode);
		$params .= "&paymentCommand.customerData.email=" . urlencode($email);
		$params .= "&paymentCommand.customerData.phoneNumber=" . urlencode($phoneNumber);
		$params .= "&paymentCommand.p1=" . urlencode($p1);
		$params .= "&paymentCommand.p2=" . urlencode($p2);
		$params .= "&paymentCommand.p3=" . urlencode($p3);
		$params .= "&paymentCommand.p4=" . urlencode($p4);
		$params .= "&paymentCommand.lang=" . $lang;

		$ouput = "";
		$ouput .= "<a target='_blank' href='" . GopayConfig::baseIntegrationURL() . "?" . $params . "'>";
		$ouput .= " Zaplatit ";
		$ouput .= "</a>";

		return $ouput;
	}

	/**
	 *  pomocne funkce pro praci s booleanem
	 */

	/**
	 * Pretypovani datoveho typu boolean pro webovou sluzbu
	 *
	 * @param boolean $boolean
	 *
	 * @return integer (0|1), v pripade nevalidniho zadani se vraci ""
	 */
	public static function castBooleanForWS($boolean) {
		$boolean = GopayHelper::castString2Boolean($boolean);

		if ($boolean === FALSE) {
			return 0;

		} else 	if ($boolean === TRUE) {
			return 1;

		} else {
			return "";
		}
	}

	/**
	 * Pretypovani datoveho typu String na boolean
	 *
	 * @param String $input
	 *
	 * @return boolean (TRUE|FALSE) v pripade spravne nastaveneho $input, jinak puvodni $input
	 */
	public static function castString2Boolean($input) {
		if (is_string($input)) {

			if (strtolower($input) == "true") {
				return TRUE;

			} else if (strtolower($input) == "false") {
				return FALSE;

			}
		}

		return $input;
	}

	/**
	 * Pretypovani datoveho typu boolean na String
	 * pouziti ve  platebnim tlacitku weboveho formulare ci odkazu
	 *
	 * @param boolean $boolean
	 *
	 * @return String ("true"|"false"), v pripade nevalidniho vstupu se vraci puvodni vstupni parametr
	 */
	public function castBoolean2String($boolean) {
	   if (is_bool($boolean)) {

		   return ($boolean) ? "true" : "false";
	   }

	   return $boolean;
	}

}
