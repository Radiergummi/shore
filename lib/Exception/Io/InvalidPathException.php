<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 08.10.2018
 * Time: 08:51
 */

namespace Shore\Framework\Exception\Io;

class InvalidPathException extends \InvalidArgumentException
{
    public function __construct(string $path)
    {
        parent::__construct("No such file: $path");
    }
}
