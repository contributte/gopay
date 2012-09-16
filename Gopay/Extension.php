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
use Nette\Config\CompilerExtension;


/**
 * Compiler extension for Nette Framework
 * 
 * @author     Vojtěch Dobeš
 * @subpackage Gopay
 */
class Extension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig();

		$driver = $container->addDefinition($this->prefix('driver'))
			->setClass('Markette\Gopay\Api\GopaySoap');

		$service = $container->addDefinition($this->prefix('service'))
			->setClass('Markette\Gopay\Service', array(
				$config,
				$driver
			));

		if (isset($config['channels'])) {
			$constants = ClassType::from('Markette\Gopay\Service');
			foreach ($config['channels'] as $channel => $value) {
				$constChannel = strtoupper($channel);
				if (isset($constants[$constChannel])) {
					$channel = $constants[$constChannel];
				}
				if (is_bool($value)) {
					$service->addSetup($value ? 'allowChannel' : 'denyChannel', $channel);
				} elseif (is_array($value)) {
					$title = $value['title'];
					unset($value['title']);
					$service->addSetup('addChannel', array(
						$channel,
						$title,
						$value
					));
				}
			}
		}
	}

}
