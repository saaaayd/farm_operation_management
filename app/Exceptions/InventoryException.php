<?php

namespace App\Exceptions;

use Exception;

class InventoryException extends Exception
{
    protected $itemId;

    public function __construct($message = 'Inventory error', $itemId = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->itemId = $itemId;
    }

    public function getItemId()
    {
        return $this->itemId;
    }
}
