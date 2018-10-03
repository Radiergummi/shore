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
    public function uri(string $append = null): string;

    public function method(): string;

    public function cli(): bool;

    public function headers(): array;

    public function header(string $key, $fallback = null): string;

    public function body(): Body;

    public function params(): Query;

    public function get(string $fieldName, $fallback = null);
}
