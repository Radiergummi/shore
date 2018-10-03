<?php

namespace Shore\Framework\Http;

use Shore\Framework\Exception\Middleware\InvalidMiddlewareException;
use Shore\Framework\HttpServerInterface;
use Shore\Framework\MiddlewareInterface;
use Shore\Framework\RequestHandlerInterface;
use Shore\Framework\RequestInterface;
use Shore\Framework\ResponseInterface;

/**
 * Middleware server
 * =================
 * Provides an HTTP "server" that will process incoming requests. The server handles the full middleware stack.
 *
 * @package Shore\Framework\Http
 */
class Server implements HttpServerInterface
{
    /**
     * Holds all loaded middlewares
     *
     * @var MiddlewareInterface[]
     */
    protected $stack = [];

    /**
     * Creates a new server. Any passed middleware will be appended to the stack, in the order they have been passed.
     *
     * @param array $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        foreach ($middlewares as $middleware) {
            $this->append($middleware);
        }
    }

    /**
     * Appends a new middleware to the stack
     *
     * @param \Shore\Framework\MiddlewareInterface|callable|\Closure $middleware
     */
    public function append($middleware): void
    {
        array_push($this->stack, $this->normalize($middleware));
    }

    /**
     * Prepends a new middleware to the stack
     *
     * @param \Shore\Framework\MiddlewareInterface|callable|\Closure $middleware
     */
    public function prepend($middleware): void
    {
        array_unshift($this->stack, $this->normalize($middleware));
    }

    /**
     * Starts the HTTP server
     *
     * @param \Shore\Framework\RequestInterface $request
     * @param callable                               $default
     *
     * @return \Shore\Framework\ResponseInterface
     */
    public function run(RequestInterface $request, callable $default): ResponseInterface
    {
        $frame = new class($this->stack, $default) implements RequestHandlerInterface
        {
            /**
             * @var MiddlewareInterface[]
             */
            private $stack = [];

            /**
             * @var int
             */
            private $index = 0;

            /**
             * @var callable
             */
            private $default;

            public function __construct(array $stack, callable $default)
            {
                $this->stack = $stack;
                $this->default = $default;
            }

            /**
             * Proceed to the next frame. This will continue to iterate through the middleware stack until a middleware
             * returns a response, which will halt the stack execution.
             *
             * @param \Shore\Framework\RequestInterface $request
             *
             * @return \Shore\Framework\ResponseInterface
             */
            public function next(RequestInterface $request): ResponseInterface
            {
                if (! isset($this->stack[$this->index])) {
                    return ($this->default)($request);
                }

                /** @var MiddlewareInterface $middleware */
                $middleware = $this->stack[$this->index];

                // Process the next frame
                return $middleware->process($request, $this->nextFrame());
            }

            private function nextFrame()
            {
                $new = clone $this;
                $new->index++;

                return $new;
            }
        };

        return $frame->next($request);
    }

    /**
     * Converts any callable middleware to an instance of CallableMiddleware (duh).
     *
     * @param callable|MiddlewareInterface $middleware Middleware to convert
     *
     * @return \Shore\Framework\MiddlewareInterface Converted Middleware
     */
    protected function normalize($middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if (is_callable($middleware)) {
            return new CallableMiddleware($middleware);
        }

        throw new InvalidMiddlewareException($middleware);
    }
}
