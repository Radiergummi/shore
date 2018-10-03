<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 02.10.2018
 * Time: 11:44
 */

namespace Shore\Framework\Exception\Middleware;

use InvalidArgumentException;

class InvalidMiddlewareException extends InvalidArgumentException
{
    public function __construct($invalidMiddleware)
    {
        parent::__construct(
            'Cannot use invalid middleware of type ' . gettype(
                $invalidMiddleware
            ) . '. Middleware must either be callable or implement the MiddlewareInterface.'
        );
    }
}
