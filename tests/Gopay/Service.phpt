<?php

/**
 * Test: Service
 * @testCase
 */

use Tester\Assert;
use Markette\Gopay\Service;
use Markette\Gopay\Payment;
use Markette\Gopay\ReturnedPayment;

require __DIR__ . '/../bootstrap.php';


class ServiceTest extends BaseTest {
	
	
	public function testPay() {
		$soap = Mockery::mock('Markette\Gopay\Api\GopaySoap');
		$soap->shouldReceive('createPayment')->once()->andReturn(3000000001);
		
		$payment = new Payment(array( 'sum' => 999, 'customer' => array()));
		$callback = function($id) {};
		
		$service = new Service($soap, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);
		$service->addChannel('eu_gb_kb','KB');
		$response = $service->pay($payment, 'eu_gb_kb', $callback);
		
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
		Assert::same('https://testgw.gopay.cz/gw/pay-full-v2?sessionInfo.targetGoId=1234567890&sessionInfo.paymentSessionId=3000000001&sessionInfo.encryptedSignature=999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
			$response->getUrl()
		);
		Assert::same(302, $response->getCode());
	}
	
	public function testUrls() {
		$service = $this->createContainer('config.neon')->getService('gopay.service');
		
		$service->setSuccessUrl('www.nette.org');
		Assert::same('http://www.nette.org', $service->getSuccessUrl());
		$service->setSuccessUrl('http://www.travis-ci.org');
		Assert::same('http://www.travis-ci.org', $service->getSuccessUrl());
		$service->setSuccessUrl( 'https://www.github.com');
		Assert::same('https://www.github.com', $service->getSuccessUrl());
		
		$service->setFailureUrl('www.nette.org');
		Assert::same('http://www.nette.org', $service->getFailureUrl());
		$service->setFailureUrl( 'http://www.travis-ci.org');
		Assert::same('http://www.travis-ci.org', $service->getFailureUrl());
		$service->setFailureUrl( 'https://www.github.com');
		Assert::same('https://www.github.com', $service->getFailureUrl());
	}
	
	public function testUnknownLangFails() {
		$service = $this->createContainer('config.neon')->getService('gopay.service');
		
		Assert::exception( function() use($service) {
			$service->setLang('de');
		}, '\InvalidArgumentException');
			
	}

	public function testCreatePayment() {
		$service = $this->createContainer('config.neon')->getService('gopay.service');
		$payment = $service->createPayment(array('sum' => 999, 'customer' => array()));
		
		Assert::type('Markette\Gopay\Payment', $payment );
	}


	public function testRestorePayment() {
		$service = $this->createContainer('config.neon')->getService('gopay.service');
		$payment = $service->restorePayment(array('sum' => 999, 'customer' => array()), array());
		
		Assert::type( 'Markette\Gopay\ReturnedPayment', $payment );
	}

	
	public function testPayExceptions() {
		$soap = Mockery::mock('Markette\Gopay\Api\GopaySoap');
		$soap->shouldReceive('createPayment')->once()->andReturn(3000000001);
		
		$payment = new Payment(array( 'sum' => 999, 'customer' => array()));
		$callback = function($id) {};
		
		$service = new Service($soap, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);
		$service->addChannel('eu_gb_kb','KB');
		
		Assert::exception( function() use( $payment, $callback, $service ) {
			$service->pay($payment, 'nonexisting', $callback);
		}, '\InvalidArgumentException', "Payment channel 'nonexisting' is not supported" );
		
		$payment = new ReturnedPayment(array( 'sum' => 999, 'customer' => array() ), 1234567890, 'fruC9a9e8ajuwrace4r3chaxu' );
		Assert::exception( function() use( $payment, $callback, $service ) {
			$service->pay( $payment, 'eu_gb_kb', $callback );
		}, '\InvalidArgumentException', "Cannot use instance of 'ReturnedPayment'! This payment has been already used for paying" );
	}

	public function testCallbackCalled() {
		$soap = Mockery::mock('Markette\Gopay\Api\GopaySoap');
		$soap->shouldReceive('createPayment')->once()->andReturn(3000000001);
		
		$payment = new Payment(array('sum' => 999, 'customer' => array()));
		$callback = function($id) use (&$called) {
			$called = true;
			Assert::same(3000000001,$id);
		};
		
		$service = new Service($soap, 1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);
		$service->addChannel('eu_gb_kb','KB');
		$service->pay($payment, 'eu_gb_kb', $callback);
		
		Assert::true($called);
	}
	
	
}


$test = new ServiceTest();
$test->run();
