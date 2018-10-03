<?php

namespace Shore\Framework;

/**
 * Response interface
 * ==================
 * This interface defines the public API for response objects.
 *
 * @package Shore\Framework
 */
interface ResponseInterface
{
    public function dispatch(): string;

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface;

    public function getStatusCode(): int;

    public function getReasonPhrase(): string;
}
