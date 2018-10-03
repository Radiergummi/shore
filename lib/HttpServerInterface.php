<?php

namespace Shore\Framework;

/**
 * HTTP server interface
 * =====================
 * This interface defines the public API of our HTTP server.
 *
 * @package Shore\Framework
 */
interface HttpServerInterface
{
    /**
     * Appends a new middleware to the stack
     *
     * @param \Shore\Framework\MiddlewareInterface|callable|\Closure $middleware
     */
    public function append($middleware): void;

    /**
     * Prepends a new middleware to the stack
     *
     * @param \Shore\Framework\MiddlewareInterface|callable|\Closure $middleware
     */
    public function prepend($middleware): void;

    /**
     * Starts the HTTP server
     *
     * @param \Shore\Framework\RequestInterface $request
     * @param callable                               $default
     *
     * @return \Shore\Framework\ResponseInterface
     */
    public function run(RequestInterface $request, callable $default): ResponseInterface;
}
