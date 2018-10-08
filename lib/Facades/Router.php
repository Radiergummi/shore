<?php

namespace Shore\Framework\Facades;

use Shore\Framework\Facade;
use Shore\Framework\Specifications\RouterInterface;

/**
 * Router facade
 * =============
 * Provides an easy way to register new routes on the router
 *
 * @method static void any(string $uri, $handler)
 * @method static void get(string $uri, $handler)
 * @method static void post(string $uri, $handler)
 * @method static void put(string $uri, $handler)
 * @method static void delete(string $uri, $handler)
 * @method static void patch(string $uri, $handler)
 * @method static void head(string $uri, $handler)
 * @method static void group(string $prefix, callable $definition)
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

    /**
     * Creates a resource route. This is essentially just a shortcut for doing it by yourself.
     *
     * @param string $name           Name of the resource, as used in the URI
     * @param string $controllerName Name of the controller class
     */
    public static function resource(string $name, string $controllerName)
    {
        static::get("/$name", "$controllerName@index");
        static::post("/$name", "$controllerName@create");
        static::get("/$name/{id}", "$controllerName@show");
        static::put("/$name/{id}", "$controllerName@update");
        static::delete("/$name/{id}", "$controllerName@destroy");
    }
}
