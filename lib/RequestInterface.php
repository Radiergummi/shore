<?php

namespace Shore\Framework;

use Shore\Framework\Http\Request\Body;
use Shore\Framework\Http\Request\Query;

/**
 * Request interface
 * =================
 *
 * This interface describes HTTP request objects. All methods are convenience methods, eg., they provide access to the
 * request details with simple methods.
 *
 * @package Shore\Framework
 */
interface RequestInterface
{
    /**
     * Retrieves the request URI
     *
     * @param string|null $append
     *
     * @return string
     */
    public function uri(string $append = null): string;

    /**
     * Retrieves the request path
     *
     * @param string|null $append
     *
     * @return string
     */
    public function path(string $append = null): string;

    /**
     * Sets the request path
     *
     * @param string $path
     *
     * @return \Shore\Framework\RequestInterface
     */
    public function withPath(string $path): RequestInterface;

    /**
     * Retrieves the request method
     *
     * @return string
     */
    public function method(): string;

    /**
     * Sets the request method
     *
     * @param string $method
     *
     * @return \Shore\Framework\RequestInterface
     */
    public function withMethod(string $method): RequestInterface;

    public function cli(): bool;

    public function headers(): array;

    public function header(string $key, $fallback = null): string;

    public function body(): Body;

    public function params(): Query;

    public function get(string $fieldName, $fallback = null);
}
