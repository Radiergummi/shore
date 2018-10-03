<?php

namespace Shore\Framework;

use Psr\Container\ContainerInterface;

/**
 * Facade base class
 * =================
 * This is the base class for facades to inherit from. It's pretty simple, really. The base class gets initialized with
 * the application instance once, storing it statically. Every other facade inherits from this class, so they will all
 * share the facade application. Therefore, we only need to know the name of the service to be able to call methods on
 * it throughout the application: Static calls, mapped to instance methods. Neat.
 *
 * @package Shore\Framework
 */
abstract class Facade
{
    /**
     * Holds the application instance
     *
     * @var ContainerInterface
     */
    protected static $application;

    /**
     * Sets the application used to resolve services
     *
     * @param \Psr\Container\ContainerInterface $application
     */
    public static function setApplication(ContainerInterface $application)
    {
        static::$application = $application;
    }

    /**
     * Proxy to statically call methods on the actual service instance
     *
     * @param string $method Name of the method to call
     * @param array  $args   Arguments passed to the method
     *
     * @return mixed Result of the method call
     */
    public static function __callStatic(string $method, array $args)
    {
        $serviceId = static::getServiceId();
        $service = static::$application->get($serviceId);

        return call_user_func_array([$service, $method], $args);
    }

    /**
     * Retrieves the service ID used to access the service on the application
     *
     * @return string
     */
    abstract public static function getServiceId(): string;
}
