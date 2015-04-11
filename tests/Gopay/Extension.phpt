<?php

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert ;
use Tester\TestCase ;
use Markette\Gopay\Extension ;

require __DIR__ . '/../bootstrap.php' ;


class ExtensionTest extends TestCase {
	
	/** @return Container */
	protected function createContainer()
	{
		$loader = new ContainerLoader(TEMP_DIR);
		$key = 'key';
		$className = $loader->load($key, function (Compiler $compiler) {
			$compiler->addExtension('gopay', new Extension());
			$compiler->loadConfig(__DIR__ . '/files/config.neon');
		});

		return new $className;
	}
	
	public function testServiceCreation()
	{
		$container = $this->createContainer();
		$service = $container->getService('gopay.service') ;

		Assert::type( 'Markette\Gopay\Service', $service );
		Assert::type( 'Markette\Gopay\Api\GopaySoap', $container->getService('gopay.driver')) ;
		
		Assert::equal( array(
			'eu_gp_u' => (object) array(
				'name' => 'eu_gp_u',
				'title' => 'METHOD_CARD_UNICREDITB',
			),
			'eu_bank' => (object) array(
				'name' => 'eu_bank',
				'title' => 'METHOD_TRANSFER',
			),
			'SUPERCASH' => (object) array(
				'name' => 'SUPERCASH',
				'title' => 'METHOD_SUPERCASH',
			),
			'custom' => (object) array(
				'image' => 'custom-channel.png',
				'name' => 'custom',
				'title' => 'Custom channel',
			)),
			$service->getChannels()
		);
	}
}


$test = new ExtensionTest() ;
$test->run() ;
