<?php

namespace Shore\Framework\Specifications;

/**
 * Response interface
 * ==================
 * This interface defines the public API for response objects.
 *
 * @package Shore\Framework\Specifications
 */
interface ResponseInterface
{
    public function dispatch(): string;

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface;

    public function getStatusCode(): int;

    public function getReasonPhrase(): string;
}
