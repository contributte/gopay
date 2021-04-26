<?php declare(strict_types = 1);

/**
 * Test: Markette\Gopay\Config
 *
 * @testCase
 */

namespace Tests\Unit;

use Markette\Gopay\Config;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../bootstrap.php';

class ConfigTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testGetters()
	{
		$config = new Config(1, 2, true);

		Assert::equal(1.0, $config->getGopayId());
		Assert::equal('2', $config->getGopaySecretKey());
		Assert::true($config->isTestMode());

		$config = new Config(11, 22, false);

		Assert::equal(11.0, $config->getGopayId());
		Assert::equal('22', $config->getGopaySecretKey());
		Assert::false($config->isTestMode());
	}

}

$test = new ConfigTest();
$test->run();
