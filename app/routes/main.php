<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 01.10.2018
 * Time: 16:21
 */

use Shore\Framework\Facades\Request;
use Shore\Framework\Facades\Router;
use Shore\Framework\RequestInterface;
use Shore\Framework\ResponseInterface;

Router::get(
    '/',
    function() {
        return "welcome home dude: " . Request::uri() . ' ' . Request::method();
    }
);

Router::get(
    '/foo/{bar}',
    function(RequestInterface $request, ResponseInterface $response, $bar) {
        return [
            'foo' => 42,
            'bar' => $bar
        ];
    }
);

Router::get(
    '/foo/bar/{id:\d+}',
    function(RequestInterface $request, $res, int $id) {
        return "/foo/{bar} has been called with " . $request->params()->get('id');
    }
);

Router::get('/controller', \MessengerPeople\Api\Controllers\TestController::class);

Router::get('/controller/{id}', 'MessengerPeople\Api\Controllers\TestController@show');

Router::resource('books', \MessengerPeople\Api\Controllers\TestController::class);
