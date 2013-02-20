<?php

namespace Markette\Gopay\Api;


class GopayHTTP
{

	/**
	 * Stazeni vypisu pohybu na uctu
	 *
	 * @param String $dateFrom - datum, od ktereho se vypis generuje
	 * @param String $dateTo - datum, do ktereho se vypis generuje
	 * Ve vypisu jsou pohyby vytvorene mezi datem dateFrom do data dateTo, vcetne techto dat
	 * @param float $targetGoId - identifikator prijemnce - GoId
	 * @param string $secureKey - kryptovaci klic prideleny GoPay
	 */
	public function getAccountStatement(
		$dateFrom,
		$dateTo,
		$targetGoId,
		$secureKey
	) {

		$contents = "";

		$encryptedSignature = GopayHelper::encrypt(
			GopayHelper::hash(
				GopayHelper::concatStatementRequest($dateFrom, $dateTo, $targetGoId, $secureKey)
			),
			$secureKey
		);

		$filename = GopayConfig::getAccountStatementURL();
		$filename .= "?statementRequest.dateFrom=" . $dateFrom;
		$filename .= "&statementRequest.dateTo=" . $dateTo;
		$filename .= "&statementRequest.targetGoId=" . $targetGoId;
		$filename .= "&statementRequest.encryptedSignature=" . $encryptedSignature;

		$handle = fopen($filename, "r");

		if (!empty($handle)) {
			while (!feof($handle)) {
				$contents .= fread($handle, 8192);
			}
			fclose($handle);
		}

		return $contents;
	}

}
