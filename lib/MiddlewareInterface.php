<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 02.10.2018
 * Time: 10:35
 */

namespace Shore\Framework;

/**
 * Provides an interface for middleware
 *
 * @package Shore\Framework
 */
interface MiddlewareInterface
{
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}
