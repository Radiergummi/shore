<?php

namespace Shore\Framework\Specifications;

/**
 * Request handler interface
 * =========================
 * This interface defines the public API of request handler frames, so the second argument any middleware receives
 * implements this interface, guaranteeing the next() method to exist.
 *
 * @package Shore\Framework\Specifications
 */
interface RequestHandlerInterface
{
    public function next(RequestInterface $request): ResponseInterface;
}
