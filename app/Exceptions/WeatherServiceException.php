<?php

namespace App\Exceptions;

use Exception;

class WeatherServiceException extends Exception
{
    protected $context;

    public function __construct($message = 'Weather service error', $context = [], $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }
}
