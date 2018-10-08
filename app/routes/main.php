<?php

use Shore\Framework\Facades\Filesystem;
use Shore\Framework\Facades\Request;
use Shore\Framework\Facades\Router;
use Shore\Framework\Specifications\RequestInterface;
use Shore\Framework\Specifications\ResponseInterface;

Router::get(
    '/',
    function() {
        $codes = Filesystem::disk('uploads')->getFile('codes.txt');

        var_dump($codes->getContent());

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



Router::get('/controller', \Shore\App\Controllers\TestController::class);

Router::get('/controller/{id}', 'Shore\App\Controllers\TestController@show');

Router::resource('books', \Shore\App\Controllers\TestController::class);
