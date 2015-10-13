<?php

/**
 * Test: Markette\Gopay\Exception\Exceptions
 *
 * @testCase
 */

use Markette\Gopay\Exception\GopayException;
use Markette\Gopay\Exception\GopayFatalException;
use Markette\Gopay\Exception\InvalidArgumentException;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class ExceptionsTest extends BaseTestCase
{

    public function testGopayException()
    {
        $message = 'Test';
        Assert::throws(function () use ($message) {
            throw new GopayException($message);
        }, 'Markette\Gopay\Exception\GopayException', $message);
    }

    public function testGopayFatalException()
    {
        $message = 'Test';
        Assert::throws(function () use ($message) {
            throw new GopayFatalException($message);
        }, 'Markette\Gopay\Exception\GopayFatalException', $message);
    }

    public function testInvalidArgumentException()
    {
        $message = 'Test';
        Assert::throws(function () use ($message) {
            throw new InvalidArgumentException($message);
        }, 'Markette\Gopay\Exception\InvalidArgumentException', $message);
    }
}

$test = new ExceptionsTest();
$test->run();
