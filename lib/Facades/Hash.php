<?php

namespace Shore\Framework\Facades;

use Shore\Framework\Facade;

/**
 * Hash facade
 * ===========
 * Provides a facade to the hashing service
 *
 * @method static string generate(int $bytes = 32)
 * @method static string from($input, bool $randomize = false, string $algorithm = \Shore\Framework\Hash::ALGORITHM_PREFERRED_NON_CRYPTO)
 * @package Shore\Framework\Facades
 */
class Hash extends Facade
{
    /**
     * Retrieves the service ID used to access the service on the application
     *
     * @return string
     */
    public static function getServiceId(): string
    {
        return \Shore\Framework\Hash::class;
    }
}
