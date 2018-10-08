<?php

use Shore\Framework\Facades\Router;

Router::group(
    '/api/v2',
    function() {
        Router::group(
            '/user',
            function() {
                Router::get('/', 'Shore\\App\\Controllers\\UserController@index');
                Router::get('/{id}', 'Shore\\App\\Controllers\\UserController@details');
            }
        );
    }
);
