<?php

/**
 * Test: Markette\Gopay\Entity\RecurrentPayment
 *
 * @testCase
 */

use Markette\Gopay\Entity\RecurrentPayment;
use Markette\Gopay\Gopay;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class RecurrentPaymentTest extends BaseTestCase
{

    public function testPayment()
    {
        $values = [
            'sum' => 999.12,
            'currency' => Gopay::CURRENCY_EUR,
        ];

        $payment = new RecurrentPayment($values);

        Assert::equal(999.12, $payment->getSum());
        Assert::equal(99912.0, $payment->getSumInCents());
        Assert::equal(Gopay::CURRENCY_EUR, $payment->getCurrency());
    }

    public function testRecurrentPayment()
    {
        $values = [
            'recurrenceCycle' => RecurrentPayment::PERIOD_DAY,
            'recurrenceDateTo' => '3000-01-01',
            'recurrencePeriod' => 10,
        ];

        $payment = new RecurrentPayment($values);

        Assert::equal(RecurrentPayment::PERIOD_DAY, $payment->getRecurrenceCycle());
        Assert::equal('3000-01-01', $payment->getRecurrenceDateTo());
        Assert::equal(10, $payment->getRecurrencePeriod());
    }

    public function testExceptions()
    {
        Assert::exception(function () {
            $payment = new RecurrentPayment(['recurrenceCycle' => 'x']);
        }, 'Markette\Gopay\Exception\InvalidArgumentException', "Not supported cycle \"x\".");
    }
}

$test = new RecurrentPaymentTest();
$test->run();
