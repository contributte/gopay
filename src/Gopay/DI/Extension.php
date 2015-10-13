<?php

namespace Markette\Gopay\DI;

use Markette\Gopay\Service\PaymentService;
use Markette\Gopay\Service\PreAuthorizedPaymentService;
use Markette\Gopay\Service\RecurrentPaymentService;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator;
use Nette\Reflection\ClassType;

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

    public function loadConfiguration()
    {
        $this->setupGopay();
        $this->setupServices();
        $this->setupForms();
    }

    private function setupGopay()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        $driver = $builder->addDefinition($this->prefix('driver'))
            ->setClass('Markette\Gopay\Api\GopaySoap');

        $helper = $builder->addDefinition($this->prefix('helper'))
            ->setClass('Markette\Gopay\Api\GopayHelper');

        $gconfig = $builder->addDefinition($this->prefix('config'))
            ->setClass('Markette\Gopay\Config', [
                $config['gopayId'],
                $config['gopaySecretKey'],
                isset($config['testMode']) ? $config['testMode'] : FALSE,
            ]);

        $builder->addDefinition($this->prefix('gopay'))
            ->setClass('Markette\Gopay\Gopay', [
                $gconfig,
                $driver,
                $helper,
            ]);
    }

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
            $def = $builder->addDefinition($this->prefix("service.$serviceName"))
                ->setClass($serviceClass, [$gopay]);

            if (is_bool($config['payments']['changeChannel'])) {
                $def->addSetup('allowChangeChannel', [$config['payments']['changeChannel']]);
            }

            if (isset($config['payments']['channels'])) {
                $constants = ClassType::from('Markette\Gopay\Gopay');
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

    private function setupForms()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('form.binder'))
            ->setClass('Markette\Gopay\Form\Binder');
    }

    /**
     * @param PhpGenerator\ClassType $class
     */
    public function afterCompile(PhpGenerator\ClassType $class)
    {
        $initialize = $class->methods['initialize'];
        $initialize->addBody('Markette\Gopay\DI\Helpers::registerAddPaymentButtonsUsingDependencyContainer($this, ?);', [
            $this->prefix('service'),
        ]);
    }

}
