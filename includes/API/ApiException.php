<?php

namespace Waca\API;

/**
 * ApiException
 */
class ApiException extends \Exception
{
    public function __construct($message)
    {
        $this->message = $message;   
    }
}
