<?php

namespace Markette\Gopay\DI;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Markette\Gopay\Config;
use Markette\Gopay\Form\Binder;
use Markette\Gopay\Gopay;
use Markette\Gopay\Service\PaymentService;
use Markette\Gopay\Service\PreAuthorizedPaymentService;
use Markette\Gopay\Service\RecurrentPaymentService;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\Reflection\ClassType as ReflectionClassType;

/**
 * Compiler extension for Nette Framework
 */
class Extension extends CompilerExtension
{

	/** @var array */
	private $defaults = [
		'gopayId' => NULL,
		'gopaySecretKey' => NULL,
		'testMode' => TRUE,
		'payments' => [
			'changeChannel' => NULL,
			'channels' => [],
		],
	];

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$this->setupGopay();
		$this->setupServices();
		$this->setupForms();
	}

	/**
	 * Register gopay services
	 *
	 * @return void
	 */
	private function setupGopay()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$driver = $builder->addDefinition($this->prefix('driver'))
			->setClass(GopaySoap::class);

		$helper = $builder->addDefinition($this->prefix('helper'))
			->setClass(GopayHelper::class);

		$gconfig = $builder->addDefinition($this->prefix('config'))
			->setClass(Config::class, [
				$config['gopayId'],
				$config['gopaySecretKey'],
				isset($config['testMode']) ? $config['testMode'] : FALSE,
			]);

		$builder->addDefinition($this->prefix('gopay'))
			->setClass(Gopay::class, [
				$gconfig,
				$driver,
				$helper,
			]);
	}

	/**
	 * Register gopay payment services
	 *
	 * @return void
	 */
	private function setupServices()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);
		$gopay = $builder->getDefinition($this->prefix('gopay'));

		$services = [
			'payment' => PaymentService::class,
			'recurrentPayment' => RecurrentPaymentService::class,
			'preAuthorizedPayment' => PreAuthorizedPaymentService::class,
		];

		foreach ($services as $serviceName => $serviceClass) {
			$def = $builder->addDefinition($this->prefix('service.' . $serviceName))
				->setClass($serviceClass, [$gopay]);

			if (is_bool($config['payments']['changeChannel'])) {
				$def->addSetup('allowChangeChannel', [$config['payments']['changeChannel']]);
			}

			if (isset($config['payments']['channels'])) {
				$constants = ReflectionClassType::from(Gopay::class);
				foreach ($config['payments']['channels'] as $code => $channel) {
					$constChannel = 'METHOD_' . strtoupper($code);
					if ($constants->hasConstant($constChannel)) {
						$code = $constants->getConstant($constChannel);
					}
					if (is_array($channel)) {
						$channel['code'] = $code;
						$def->addSetup('addChannel', $channel);
					} else if (is_scalar($channel)) {
						$def->addSetup('addChannel', [$code, $channel]);
					}
				}
			}
		}
	}

	/**
	 * Register form services
	 *
	 * @return void
	 */
	private function setupForms()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('form.binder'))
			->setClass(Binder::class);
	}

	/**
	 * @param ClassType $class
	 * @return void
	 */
	public function afterCompile(ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$initialize->addBody('Markette\Gopay\DI\Helpers::registerAddPaymentButtonsUsingDependencyContainer($this, ?);', [
			$this->prefix('service'),
		]);
	}

}
