<?php

namespace Shore\Framework\Exception\Route;

class NotFoundException extends RouteHandlerException
{
    public function __construct($requestUri)
    {
        parent::__construct("The requested resource at $requestUri does not exist", 404);
    }
}
