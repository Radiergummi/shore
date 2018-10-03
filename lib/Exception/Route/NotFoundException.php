<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 01.10.2018
 * Time: 14:29
 */

namespace Shore\Framework\Exception\Route;

class NotFoundException extends RouteHandlerException
{
    public function __construct($requestUri)
    {
        parent::__construct("The requested resource at $requestUri does not exist", 404);
    }
}
