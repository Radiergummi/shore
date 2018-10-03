<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 01.10.2018
 * Time: 12:06
 */

namespace Shore\Framework;

interface ResponseInterface
{
    public function dispatch(): string;

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface;

    public function getStatusCode(): int;

    public function getReasonPhrase(): string;
}
