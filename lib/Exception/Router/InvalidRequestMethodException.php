<?php

namespace Shore\Framework\Exception\Router;

use InvalidArgumentException;

class InvalidRequestMethodException extends InvalidArgumentException
{
    public function __construct($method)
    {
        parent::__construct("Invalid request method $method", 400);
    }
}
