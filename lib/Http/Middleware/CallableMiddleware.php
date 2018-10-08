<?php

namespace Shore\Framework\Http\Middleware;

use Shore\Framework\Specifications\MiddlewareInterface;
use Shore\Framework\Specifications\RequestHandlerInterface;
use Shore\Framework\Specifications\RequestInterface;
use Shore\Framework\Specifications\ResponseInterface;

/**
 * Callable middleware
 * ===================
 * The callable middleware wraps around a callable and creates a middleware instance that simply calls the callable.
 *
 * @package Shore\Framework\Http
 */
class CallableMiddleware implements MiddlewareInterface
{
    /**
     * Holds the middleware callable
     *
     * @var callable
     */
    protected $callable;

    public function __construct(callable $middleware)
    {
        $this->callable = $middleware;
    }

    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callable = $this->callable;

        return $callable($request, $handler);
    }
}
