<?php

namespace Shore\Framework;

/**
 * Middleware interface
 * ====================
 * Provides an interface for middleware that is - apart from the PSR-7 messages - equivalent to PSR-15.
 *
 * @package Shore\Framework
 */
interface MiddlewareInterface
{
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}
