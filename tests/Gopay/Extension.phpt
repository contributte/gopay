<?php

use Tester\Assert ;

require __DIR__ . '/../bootstrap.php' ;


class ExtensionTest extends BaseTest {
	
	public function testServiceCreation() {
		$container = $this->createContainer('config.neon');
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
