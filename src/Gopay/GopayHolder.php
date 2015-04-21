<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;


/**
 * GopayHolder - singleton - hold helper and soap instances.
 *
 * @author Vojtěch Dobeš
 * @author Jan Skrasek
 */
class GopayHolder
{

    /** @var GopayHolder */
    private static $instance;

    /** @var GopayHelper */
    private static $helper;

    /** @var GopaySoap */
    private static $soap;

    private function __construct()
    {
        // Disable constructor
    }

    /**
     * @return GopayHolder
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$helper = new GopayHelper();
            self::$soap = new GopaySoap();
        }

        return self::$instance;
    }

    /**
     * @return GopayHelper
     */
    public function getHelper() {
        return self::$helper;
    }

    /**
     * @param mixed $helper
     */
    public function setHelper($helper) {
        self::$helper = $helper;
    }

    /**
     * @return GopaySoap
     */
    public function getSoap()
    {
        return self::$soap;
    }

    /**
     * @param mixed $soap
     */
    public function setSoap($soap)
    {
        self::$soap = $soap;
    }

}
