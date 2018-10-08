<?php

namespace Shore\Framework\Specifications;

use Shore\Framework\Routing\Route;

/**
 * Router interface
 * =================
 * This interface allows you to swap the router definition, should you want to roll your own. The neat thing about this
 * is that shore is completely built around this interface, so even the facade API stays the same if you use another.
 *
 * @package Shore\Framework\Specifications
 */
interface RouterInterface
{
    public function match(RequestInterface $request): Route;

    public function any(string $uri, $handler): void;

    public function get(string $uri, $handler): void;

    public function post(string $uri, $handler): void;

    public function put(string $uri, $handler): void;

    public function delete(string $uri, $handler): void;

    public function patch(string $uri, $handler): void;

    public function head(string $uri, $handler): void;
}
