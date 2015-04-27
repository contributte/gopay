<?php

/**
 * Markette - payment methods integration for Nette Framework
 *
 * @license New BSD
 * @package Markette
 * @author  Vojtěch Dobeš
 */

namespace Markette\Gopay;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator;
use Nette\Reflection\ClassType;



/**
 * Compiler extension for Nette Framework
 *
 * @author Vojtěch Dobeš
 * @author Jan Skrasek
 */
class Extension extends CompilerExtension
{

	/** @var array */
	private $defaults = array(
		'gopayId' => NULL,
		'gopaySecretKey' => NULL,
		'testMode' => TRUE,
		'channels' => array(),
	);

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$driver = $container->addDefinition($this->prefix('driver'))
			->setClass('Markette\Gopay\Api\GopaySoap');

		$service = $container->addDefinition($this->prefix('service'))
			->setClass('Markette\Gopay\Service', array(
				$driver,
				$config['gopayId'],
				$config['gopaySecretKey'],
				isset($config['testMode']) ? $config['testMode'] : FALSE
			));

		if (isset($config['channels'])) {
			$constants = ClassType::from('Markette\Gopay\Service');
			foreach ($config['channels'] as $code => $channel) {
				$constChannel = 'METHOD_' . strtoupper($code);
				if ($constants->hasConstant($constChannel)) {
					$code = $constants->getConstant($constChannel);
				}
				if (is_array($channel)) {
					$channel['code'] = $code;
					$service->addSetup('addChannel', $channel);
				} else if (is_scalar($channel)) {
					$service->addSetup('addChannel', array($code, $channel));
				}
			}
		}
	}



	public function afterCompile(PhpGenerator\ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$initialize->addBody('Markette\Gopay\Service::registerAddPaymentButtonsUsingDependencyContainer($this, ?);', array(
			$this->prefix('service'),
		));
	}

}
