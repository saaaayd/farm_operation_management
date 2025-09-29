<?php

namespace App\Exceptions;

use Exception;

class MarketplaceException extends Exception
{
    protected $orderId;

    public function __construct($message = 'Marketplace error', $orderId = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->orderId = $orderId;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }
}
