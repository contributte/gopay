<?php

use Markette\Gopay\Config;
use Markette\Gopay\Gopay;
use Mockery\Exception\InvalidCountException;
use Mockery\MockInterface;
use Tester\Assert;

class BasePaymentTestCase extends BaseTestCase
{

    /** @var MockInterface[] */
    private $mocks = [];

    /**
     * @return Gopay
     */
    protected function createPaymentGopay()
    {
        $soap = Mockery::namedMock('GopaySoap1' . md5(microtime()), 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPayment')->once()->andReturn(3000000001)->byDefault();

        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $config = new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);

        $gopay = new Gopay($config, $soap, $helper);

        $this->mocks[] = $soap;
        $this->mocks[] = $helper;

        return $gopay;
    }

    /**
     * @return Gopay
     */
    protected function createRecurrentPaymentGopay()
    {
        $soap = Mockery::namedMock('GopaySoap2' . md5(microtime()), 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createRecurrentPayment')->once()->andReturn(3000000001)->byDefault();

        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $config = new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);

        $gopay = new Gopay($config, $soap, $helper);

        $this->mocks[] = $soap;
        $this->mocks[] = $helper;

        return $gopay;
    }

    /**
     * @return Gopay
     */
    protected function createPreAuthorizedPaymentGopay()
    {
        $soap = Mockery::namedMock('GopaySoap3' . md5(microtime()), 'Markette\Gopay\Api\GopaySoap');
        $soap->shouldReceive('createPreAutorizedPayment')->once()->andReturn(3000000001)->byDefault();

        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $config = new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);

        $gopay = new Gopay($config, $soap, $helper);

        $this->mocks[] = $soap;
        $this->mocks[] = $helper;

        return $gopay;
    }

    /**
     * @return Gopay
     */
    protected function createGopay()
    {
        $soap = Mockery::namedMock('GopaySoap4' . md5(microtime()), 'Markette\Gopay\Api\GopaySoap');
        $helper = Mockery::mock('Markette\Gopay\Api\GopayHelper');

        $config = new Config(1234567890, 'fruC9a9e8ajuwrace4r3chaxu', TRUE);

        $gopay = new Gopay($config, $soap, $helper);

        $this->mocks[] = $soap;
        $this->mocks[] = $helper;

        return $gopay;
    }

    /**
     * @return Closure
     */
    protected function createNullCallback()
    {
        return function () {
        };
    }

    /**
     * Tear down and verify all mocks
     */
    protected function tearDown()
    {
        parent::tearDown();

        try {
            foreach ($this->mocks as $mock) {
                $mock->mockery_verify();
            }
        } catch (InvalidCountException $e) {
            Assert::fail($e->getMessage());
        }
    }


}

