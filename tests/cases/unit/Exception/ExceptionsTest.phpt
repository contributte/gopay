<?php

/**
 * Test: Markette\Gopay\Exception\Exceptions
 *
 * @testCase
 */

namespace Tests\Unit\Exception;

use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Exception\GopayFatalException;
use Markette\Gopay\Exception\InvalidArgumentException;
use Tester\Assert;
use Tests\Engine\BaseTestCase;

require __DIR__ . '/../../../bootstrap.php';

class ExceptionsTest extends BaseTestCase
{

	/**
	 * @test
	 * @return void
	 */
	public function testGopayException()
	{
		$message = 'Test';
		Assert::throws(function () use ($message) {
			throw new GopayException($message);
		}, GopayException::class, $message);
	}

	/**
	 * @test
	 * @return void
	 */
	public function testGopayFatalException()
	{
		$message = 'Test';
		Assert::throws(function () use ($message) {
			throw new GopayFatalException($message);
		}, GopayFatalException::class, $message);
	}

	/**
	 * @test
	 * @return void
	 */
	public function testInvalidArgumentException()
	{
		$message = 'Test';
		Assert::throws(function () use ($message) {
			throw new InvalidArgumentException($message);
		}, InvalidArgumentException::class, $message);
	}

}

$test = new ExceptionsTest();
$test->run();
