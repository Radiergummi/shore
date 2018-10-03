<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 01.10.2018
 * Time: 12:47
 */

namespace Shore\Framework\Exception\Router;

use InvalidArgumentException;

class InvalidRequestMethodException extends InvalidArgumentException
{
    public function __construct($method)
    {
        parent::__construct("Invalid request method $method", 400);
    }
}
