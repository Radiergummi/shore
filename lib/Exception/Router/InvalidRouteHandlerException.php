<?php

namespace Shore\Framework\Exception\Router;

use InvalidArgumentException;

class InvalidRouteHandlerException extends InvalidArgumentException
{
    public function __construct($routeHandler)
    {
        if (is_string($routeHandler)) {
            parent::__construct("Cannot resolve route handler $routeHandler to a controller class");
        } else {
            parent::__construct("Cannot process route handler of type " . gettype($routeHandler));
        }
    }
}
