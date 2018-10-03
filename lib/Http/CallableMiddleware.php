<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 02.10.2018
 * Time: 11:30
 */

namespace Shore\Framework\Http;

use Shore\Framework\MiddlewareInterface;
use Shore\Framework\RequestHandlerInterface;
use Shore\Framework\RequestInterface;
use Shore\Framework\ResponseInterface;

class CallableMiddleware implements MiddlewareInterface
{
    protected $callable;

    public function __construct($middleware)
    {
        $this->callable = $middleware;
    }

    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callable = $this->callable;

        return $callable($request, $handler);
    }
}
