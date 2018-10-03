<?php

namespace Shore\Framework\Exception;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

class ServiceMissingException extends InvalidArgumentException implements NotFoundExceptionInterface
{

}
