<?php

namespace Shore\Framework\Facades;

use Shore\Framework\Facade;
use Shore\Framework\Specifications\RouterInterface;

/**
 * Router facade. Provides an easy way to register new routes on the router
 *
 * @method static void any(string $uri, $handler)
 * @method static void get(string $uri, $handler)
 * @method static void post(string $uri, $handler)
 * @method static void put(string $uri, $handler)
 * @method static void delete(string $uri, $handler)
 * @method static void patch(string $uri, $handler)
 * @method static void head(string $uri, $handler)
 * @method static void resource(string $name, string $controller)
 * @method static void group(string $prefix, callable $callback)
 *
 * @package Shore\Framework\Facades
 */
class Router extends Facade
{
    /**
     * Retrieves the service ID used to access the service on the application
     *
     * @return string
     */
    public static function getServiceId(): string
    {
        return RouterInterface::class;
    }
}
