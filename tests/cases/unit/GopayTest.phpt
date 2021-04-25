<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\Gopay
 *
 * @testCase
 */

namespace Tests\Unit;

use Markette\Gopay\Api\GopayHelper;
use Markette\Gopay\Api\GopaySoap;
use Markette\Gopay\Config;
use Markette\Gopay\Gopay;
use Mockery;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../bootstrap.php';

class GopayTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testGetters()
	{
		$config = Mockery::mock(Config::class);
		$soap = Mockery::mock(GopaySoap::class);
		$helper = Mockery::mock(GopayHelper::class);

		$gopay = new Gopay($config, $soap, $helper);

		Assert::same($config, $gopay->getConfig());
		Assert::same($soap, $gopay->getSoap());
		Assert::same($helper, $gopay->getHelper());
	}

}

$test = new GopayTest();
$test->run();
