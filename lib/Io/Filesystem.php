<?php

namespace Shore\Framework\Io;

use Exception;

class Filesystem extends Directory
{
    public function __construct(string $root)
    {
        parent::__construct($root, true);
    }

    /**
     * No-op handler - can't delete the filesystem.
     *
     * @throws \Exception
     */
    public function delete(): void
    {
        throw new Exception('Cannot delete the root file system');
    }
}
