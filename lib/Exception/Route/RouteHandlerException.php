<?php

namespace Shore\Framework\Exception\Route;

use Exception;

/**
 * The route handler exception is the generic prototype for all kinds of exceptions to be thrown while processing
 * requests. It is extended by multiple, more specific exceptions.
 *
 * @package Shore\Framework\Exception\Route
 */
abstract class RouteHandlerException extends Exception
{
}
