<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 28.09.2018
 * Time: 15:06
 */

namespace Shore\Framework\Routing;

use Closure;
use Shore\Framework\ControllerInterface;
use Shore\Framework\Exception\Router\InvalidRouteHandlerException;

class Route
{
    public const CONTROLLER_DELIMITER = '@';

    /**
     * Holds the request URI
     *
     * @var string
     */
    protected $uri;

    /**
     * Holds the route handler
     *
     * @var \Closure
     */
    protected $handler;

    /**
     * Holds all URI arguments the route has
     *
     * @var array
     */
    protected $args = [];

    public function __construct(string $uri, $handler)
    {
        $this->uri = $uri;
        $this->handler = $handler;
    }

    /**
     * Sets the route arguments
     *
     * @param array $args
     *
     * @return \Shore\Framework\Routing\Route
     */
    public function withArgs(array $args): Route
    {
        $this->args = $args;

        return $this;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getHandler()
    {
        // If we received a callable, return immediately
        if (is_callable($this->handler)) {
            return Closure::fromCallable($this->handler);
        }

        // If we did receive neither a callable nor a string, this is just a wrong argument, so bail
        if (! is_string($this->handler)) {
            throw new InvalidRouteHandlerException($this->handler);
        }

        // If there is no delimiter character in the handler string, this will either be a controller with a single
        // `__invoke()` method or another wrong argument.
        if (strpos($this->handler, static::CONTROLLER_DELIMITER) === false) {
            // No such class - bail.
            if (! class_exists($this->handler)) {
                throw new InvalidRouteHandlerException($this->handler);
            }

            // Create a new controller instance
            $controller = new $this->handler();

            // Not callable - bail.
            if (! is_callable($controller)) {
                throw new InvalidRouteHandlerException($this->handler);
            }

            // Finally, return the controller instance
            return Closure::fromCallable($controller);
        }

        // Split the handler string at the delimiter character, so we receive class and method
        list ($className, $method) = explode(static::CONTROLLER_DELIMITER, $this->handler);

        // No such class - bail.
        if (! class_exists($className)) {
            throw new InvalidRouteHandlerException($className);
        }

        // Create a new instance of the controller
        $controller = new $className();

        // No such method - bail.
        if (
            ! $controller instanceof ControllerInterface ||
            ! method_exists($controller, $method)
        ) {
            throw new InvalidRouteHandlerException($this->handler);
        }

        // Return a closure
        return Closure::fromCallable([$controller, $method]);
    }
}
