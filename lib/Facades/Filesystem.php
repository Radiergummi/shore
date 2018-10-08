<?php

namespace Shore\Framework\Facades;

use Shore\Framework\Facade;
use Shore\Framework\Io\Filesystem as FS;

/**
 * Class Filesystem
 *
 * @package Shore\Framework\Facades
 */
class Filesystem extends Facade
{
    /**
     * Retrieves the service ID used to access the service on the application
     *
     * @return string
     */
    public static function getServiceId(): string
    {
        return 'filesystem';
    }

    /**
     * Retrieves a named disk from the filesystem
     *
     * @param string $diskName
     *
     * @return \Shore\Framework\Io\Filesystem
     */
    public static function disk(string $diskName): FS
    {
        return static::$application->get("filesystem:$diskName");
    }
}
