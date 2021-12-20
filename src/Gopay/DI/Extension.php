<?php declare(strict_types = 1);

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
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use ReflectionClass;

/**
 * Compiler extension for Nette Framework
 */
class Extension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'gopayId' => Expect::int()->nullable(),
			'gopaySecretKey' => Expect::string()->nullable(),
			'testMode' => Expect::bool(),
			'payments' => Expect::structure([
				'changeChannel' => Expect::bool(),
				'channels' => Expect::array()->nullable(),
			]),
		]);
	}

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$this->setupGopay();
		$this->setupServices();
		$this->setupForms();
	}

	/**
	 * Register gopay services
	 */
	private function setupGopay(): void
	{
		$builder = $this->getContainerBuilder();
		$config = (array) $this->getConfig();

		$driver = $builder->addDefinition($this->prefix('driver'))
			->setType(GopaySoap::class);

		$helper = $builder->addDefinition($this->prefix('helper'))
			->setType(GopayHelper::class);

		$gconfig = $builder->addDefinition($this->prefix('config'))
			->setFactory(Config::class, [
				$config['gopayId'],
				$config['gopaySecretKey'],
				$config['testMode'] ?? false,
			]);

		$builder->addDefinition($this->prefix('gopay'))
			->setFactory(Gopay::class, [
				$gconfig,
				$driver,
				$helper,
			]);
	}

	/**
	 * Register gopay payment services
	 */
	private function setupServices(): void
	{
		$builder = $this->getContainerBuilder();
		$config = (array) $this->getConfig();
		$gopay = $builder->getDefinition($this->prefix('gopay'));

		$services = [
			'payment' => PaymentService::class,
			'recurrentPayment' => RecurrentPaymentService::class,
			'preAuthorizedPayment' => PreAuthorizedPaymentService::class,
		];

		foreach ($services as $serviceName => $serviceClass) {
			$def = $builder->addDefinition($this->prefix('service.' . $serviceName))
				->setFactory($serviceClass, [$gopay]);

			if (is_bool($config['payments']['changeChannel'])) {
				$def->addSetup('allowChangeChannel', [$config['payments']['changeChannel']]);
			}

			if (isset($config['payments']['channels'])) {
				$constants = new ReflectionClass(Gopay::class);
				foreach ($config['payments']['channels'] as $code => $channel) {
					$constChannel = 'METHOD_' . strtoupper($code);
					if ($constants->hasConstant($constChannel)) {
						$code = $constants->getConstant($constChannel);
					}

					if (is_array($channel)) {
						$channel['code'] = $code;
						$def->addSetup('addChannel', $channel);
					} elseif (is_scalar($channel)) {
						$def->addSetup('addChannel', [$code, $channel]);
					}
				}
			}
		}
	}

	/**
	 * Register form services
	 */
	private function setupForms(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('form.binder'))
			->setType(Binder::class);
	}

	/**
	 * @param ClassType $class
	 */
	public function afterCompile(ClassType $class): void
	{
		$initialize = $class->methods['initialize'];
		$initialize->addBody('Markette\Gopay\DI\Helpers::registerAddPaymentButtonsUsingDependencyContainer($this, ?);', [
			$this->prefix('service'),
		]);
	}

}
