<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 02.10.2018
 * Time: 10:39
 */

namespace Shore\Framework;

interface RequestHandlerInterface
{
    public function next(RequestInterface $request): ResponseInterface;
}
