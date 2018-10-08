<?php

namespace Shore\Framework\Specifications;

use Shore\Framework\Http\Request\Body;
use Shore\Framework\Http\Request\Query;
use Shore\Framework\Http\Uri;

/**
 * Request interface
 * =================
 *
 * This interface describes HTTP request objects. All methods are convenience methods, eg., they provide access to the
 * request details with simple methods.
 *
 * @package Shore\Framework\Specifications
 */
interface RequestInterface
{
    /**
     * Retrieves the request URI
     *
     * @return \Shore\Framework\Http\Uri
     */
    public function uri(): Uri;

    /**
     * Retrieves the request path
     *
     * @param string|null $append
     *
     * @return string
     */
    public function path(string $append = ''): string;

    /**
     * Sets the request path
     *
     * @param string $path
     *
     * @return \Shore\Framework\Specifications\RequestInterface
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
     * @return \Shore\Framework\Specifications\RequestInterface
     */
    public function withMethod(string $method): RequestInterface;

    public function cli(): bool;

    public function headers(): array;

    public function header(string $key, $fallback = null): string;

    public function body(): Body;

    public function params(): Query;

    public function get(string $fieldName, $fallback = null);
}
