<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 01.10.2018
 * Time: 11:03
 */

namespace Shore\Framework\Exception;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

class ServiceMissingException extends InvalidArgumentException implements NotFoundExceptionInterface
{

}
