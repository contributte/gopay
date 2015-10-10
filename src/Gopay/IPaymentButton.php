<?php

namespace Markette\Gopay;

interface IPaymentButton
{

    /**
     * Returns name (title) of payment channel.
     *
     * @return string
     */
    function getChannel();

}
