<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 01.10.2018
 * Time: 11:26
 */

namespace Shore\Framework;

use Shore\Framework\Routing\Route;

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
