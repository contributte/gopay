<?php

use Tester\Assert ;
use Markette\Gopay\Service ;
use Markette\Gopay\Payment ;

require __DIR__ . '/../bootstrap.php' ;


class ServiceTest extends BaseTest {
	
	
	public function testPay() {
		$soap = Mockery::mock('Markette\Gopay\Api\GopaySoap') ;
		$soap->shouldReceive('createPayment')->once()->andReturn(3000000001);
		
		$payment = new Payment( array( 'sum' => 999, 'customer' => array() )) ;
		$callback = function($id) {} ;
		
		$service = new Service( $soap, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE ) ;
		$service->addChannel('eu_gb_kb','KB');
		$response = $service->pay( $payment, 'eu_gb_kb', $callback ) ;
		
		Assert::type( 'Nette\Application\Responses\RedirectResponse', $response ) ;
		Assert::same( 'https://testgw.gopay.cz/gw/pay-full-v2?sessionInfo.targetGoId=1234567890&sessionInfo.paymentSessionId=3000000001&sessionInfo.encryptedSignature=999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
			$response->getUrl()
		);
		Assert::same( 302, $response->getCode()) ;
	}
	
	public function testDenyChannel() {
		$service = $this->createContainer('config.neon')->getService('gopay.service') ;
		$service->denyChannel('SUPERCASH') ;
		
		Assert::equal( array(
			'eu_gp_u' => (object) array(
				'name' => 'eu_gp_u',
				'title' => 'METHOD_CARD_UNICREDITB',
			),
			'eu_bank' => (object) array(
				'name' => 'eu_bank',
				'title' => 'METHOD_TRANSFER',
			),
			'custom' => (object) array(
				'image' => 'custom-channel.png',
				'name' => 'custom',
				'title' => 'Custom channel',
			)),
		$service->getChannels()) ;
	}
	
	public function testAllowChannel() {
		$service = $this->createContainer('config.neon')->getService('gopay.service') ;
		$service->allowChannel('cz_kb') ;
		
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
			),
			'cz_kb' => (object) array(
				'name' => 'cz_kb',
				'title' => 'METHOD_KOMERCNIB',
			)),
		$service->getChannels()) ;
	}
	
	public function testUrls() {
		$service = $this->createContainer('config.neon')->getService('gopay.service') ;
		
		$service->setSuccessUrl( 'www.nette.org' ) ;
		Assert::same( 'http://www.nette.org', $service->getSuccessUrl());
		$service->setSuccessUrl( 'http://www.travis-ci.org' ) ;
		Assert::same( 'http://www.travis-ci.org', $service->getSuccessUrl());
		$service->setSuccessUrl( 'https://www.github.com' ) ;
		Assert::same( 'https://www.github.com', $service->getSuccessUrl());
		
		$service->setFailureUrl( 'www.nette.org' ) ;
		Assert::same( 'http://www.nette.org', $service->getFailureUrl());
		$service->setFailureUrl( 'http://www.travis-ci.org' ) ;
		Assert::same( 'http://www.travis-ci.org', $service->getFailureUrl());
		$service->setFailureUrl( 'https://www.github.com' ) ;
		Assert::same( 'https://www.github.com', $service->getFailureUrl());
	}
	
	public function testLang() {
		$service = $this->createContainer('config.neon')->getService('gopay.service') ;
		
		Assert::exception( function() use($service) {
			$service->setLang('de');
		}, '\InvalidArgumentException') ;
			
	}
}


$test = new ServiceTest() ;
$test->run() ;
