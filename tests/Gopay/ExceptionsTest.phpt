<?php

/**
 * Test: Exceptions
 *
 * @testCase
 */

use Markette\Gopay\GopayException;
use Markette\Gopay\GopayFatalException;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class ExceptionsTest extends BaseTestCase
{

    public function testGopayException()
    {
        $message = 'Test';
        Assert::throws(function () use ($message) {
            throw new GopayException($message);
        }, 'Markette\Gopay\GopayException', $message);
    }

    public function testGopayFatalException()
    {
        $message = 'Test';
        Assert::throws(function () use ($message) {
            throw new GopayFatalException($message);
        }, 'Markette\Gopay\GopayFatalException', $message);
    }
}

$test = new ExceptionsTest();
$test->run();
