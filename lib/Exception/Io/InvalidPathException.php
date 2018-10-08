<?php

namespace Shore\Framework\Exception\Io;

class InvalidPathException extends \InvalidArgumentException
{
    public function __construct(string $path)
    {
        parent::__construct("No such file: $path");
    }
}
