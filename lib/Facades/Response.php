<?php

namespace Shore\Framework\Facades;

use Shore\Framework\Facade;
use Shore\Framework\ResponseInterface;

/**
 * Response Facade
 * ===============
 *
 * @method static ResponseInterface create(string $body = '', int $code = 200, array $headers = [])
 * @method static ResponseInterface redirect(string $target = '', int $code = 301, array $headers = [])
 * @package Shore\Framework\Facades
 */
class Response extends Facade
{
    /**
     * Retrieves the service ID used to access the service on the application
     *
     * @return string
     */
    public static function getServiceId(): string
    {
        return ResponseInterface::class;
    }
}
