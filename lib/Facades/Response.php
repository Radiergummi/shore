<?php

namespace Shore\Framework\Facades;

use Shore\Framework\Facade;
use Shore\Framework\ResponseInterface;

class Response extends Facade
{
    /**
     * Retrieves the service ID used to access the service on the application
     *
     * @return string
     */
    public static function getServiceId(): string
    {
        return ResponseInterface::class;
    }
}
