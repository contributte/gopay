<?php

use Tester\Assert ;
use Tester\TestCase ;
use Markette\Gopay\Service ;
use Markette\Gopay\Payment ;

require __DIR__ . '/../bootstrap.php' ;


class ServiceTest extends TestCase {
	
	
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
}


$test = new ServiceTest() ;
$test->run() ;
