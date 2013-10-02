<?php

namespace Markette\Gopay\Api;


class GopayHTTP {

	/**
	 * Stazeni vypisu pohybu na uctu
	 *
	 * Ve vypisu jsou pohyby vytvorene mezi datem dateFrom do data dateTo, vcetne techto datumu
	 * @param String $dateFrom - datum, od ktereho se vypis generuje
	 * @param String $dateTo - datum, do ktereho se vypis generuje
	 * @param float $targetGoId - identifikator prijemnce - GoId
	 * @param String $currency - mena uctu, ze ktereho se vypis pohybu ziskava
	 * @param string $contentType - format vypisu - podporovane typt - TYPE_CSV, TYPE_XLS, TYPE_ABO, implicitni je hodnota TYPE_CSV
	 * @param string $secureKey - kryptovaci klic prideleny GoPay
	 */
	public function getAccountStatement($dateFrom,
										$dateTo,
										$targetGoId,
										$currency,
										$secureKey,
										$contentType) {

		$encryptedSignature = GopayHelper::encrypt(
										GopayHelper::hash(
												GopayHelper::concatStatementRequest($dateFrom, $dateTo, $targetGoId, $currency, $secureKey)
										), $secureKey);

		$filename  = GopayConfig::getAccountStatementURL();
		$filename .= "?statementRequest.dateFrom=" . $dateFrom;
		$filename .= "&statementRequest.dateTo=" . $dateTo;
		$filename .= "&statementRequest.targetGoId=" . $targetGoId;
		$filename .= "&statementRequest.currency=" . $currency;
		$filename .= "&statementRequest.contentType=" . $contentType;
		$filename .= "&statementRequest.encryptedSignature=" . $encryptedSignature;

		echo $filename;

		$handle = fopen($filename, "r");

		$contents = "";

		if (! empty($handle)) {

			while (! feof($handle)) {
				$contents .= fread($handle, 8192);
			}

			fclose($handle);
		}

		return $contents;
	}

}
