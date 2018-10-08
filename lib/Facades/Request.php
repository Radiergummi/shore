<?php

namespace Shore\Framework\Facades;

use Shore\Framework\Facade;
use Shore\Framework\Specifications\RequestInterface;

/**
 * Request facade. Provides an easy way to access request properties.
 *
 * @method static string method()
 * @method static string uri(string $append = null)
 * @method static \Shore\Framework\Http\Request\Query params()
 * @method static \Shore\Framework\Http\Request\Body body()
 * @package Shore\Framework\Facades
 */
class Request extends Facade
{
    /**
     * Retrieves the service ID used to access the service on the application
     *
     * @return string
     */
    public static function getServiceId(): string
    {
        return RequestInterface::class;
    }
}
