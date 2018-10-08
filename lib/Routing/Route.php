<?php

namespace Shore\Framework\Routing;

use Shore\Framework\Controller\CallableController;
use Shore\Framework\Specifications\ControllerInterface;
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
     * @var ControllerInterface
     */
    protected $handler;

    /**
     * Holds the route handler method name
     *
     * @var string
     */
    protected $method;

    /**
     * Holds all URI arguments the route has
     *
     * @var array
     */
    protected $args = [];

    public function __construct(string $uri, $handler)
    {
        $this->uri = rtrim($uri, '/') ?: '/';
        $this->loadHandler($handler);
    }

    /**
     * Retrieves the route URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
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

    /**
     * Retrieves the route arguments
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Loads the route handler
     *
     * @param $handler
     */
    public function loadHandler($handler): void
    {
        // If we received a callable, convert it into a controller
        if (is_callable($handler)) {
            $this->handler = new CallableController($handler);
            $this->method = '__invoke';

            return;
        }

        // If we did receive neither a callable nor a string, this is just a wrong argument, so bail
        if (! is_string($handler)) {
            throw new InvalidRouteHandlerException($handler);
        }

        // Split the handler string at the delimiter character, so we receive class and method
        $segments = explode(static::CONTROLLER_DELIMITER, $handler);

        // Set class name and method. If no method has been specified, default to __invoke
        $className = $segments[0];
        $method = $segments[1] ?? '__invoke';

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
            throw new InvalidRouteHandlerException($handler);
        }

        $this->handler = $controller;
        $this->method = $method;
    }

    /**
     * Retrieves the controller instance
     *
     * @return ControllerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Retrieves the method instance
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
