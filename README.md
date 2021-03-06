# Shore
A lean framework for PHP-driven APIs

--------

Shore is an opinionated framework for building APIs. It get's out of your way mostly, as it only takes care of handling 
requests and responses.  
As any other web framework, Shore lets you define _routes_ that can be hooked to closures or controllers. Route handlers
will always be called in context of the application container, which holds the services you registered.  
Of course, there's support for middleware, too. 

**Beware, though: Shore does _not_ implement PSR-7 HTTP messages. If that's a killer for you, I'm sorry.** The 
reasoning being, I find the responses way too bloated. I don't want to use streams as response bodies, but that prevents
me from implementing PSR-7 all together, as well as the PSR-15 middleware standard - since that relies on PSR-7.

So if you're looking for a small alternative to Laravel, Symfony at al to build your system upon however, you've come to 
the right place.

## Features
 - Shore is _fast_: Currently, the whole framework stack takes around 0.01s to execute, including setup, route matching
    and sending the responses.
 - A framework you can understand: Take a look at the code, hack it, whatever. Everything's simple, thoroughly 
   documented and open for exploration.
 - No fixed layout whatsoever: There are three constants in the [index.php](./public/index.php) that define where
   the code for your application is to be found. Just add some routes and build up the layout you desire.
 - Real-world way to do things: No, Shore won't let you inject five different dispatchers to send XML/JSON/RPC/HTML. 
   Sorry. It sends JSON or strings, just like that.

## Getting started
Clone this repository at the root of your new project, add routes in the main route file and you're ready. To follow the
flow of requests, start at [`public/index.php`](./public/index.php). Comments will guide you.

## Defining routes
All routing has to happen in the main routes file which is `require_once`'d in [`app/bootstrap`](./app/bootstrap.php).  
Let's look at an example:  

```php
<?php
use \Shore\Framework\Facades\Router;

// That route will just call your callable with request and response objects.
Router::get('/welcome', function($req, $res) {
    return 'hi there!';
});

// That route will call `index` on your `BooksController`.
Router::get('/books', '\Shore\App\Controllers\BooksController@index');

// That route expects your controller to be callable, that is, having an `__invoke` method.
Router::get('/ping', \Shore\App\Controllers\PingController::class);
```

You're free to use each of the three handler definition types as you wish. Of course, there's also support for 
placeholders:

```php
<?php
use \Shore\Framework\Facades\Router;

// Your controller will receive the title as its third argument, or via $request->get('title')
Router::get('/books/{title}', '\Shore\App\Controllers\BooksController@byTitle');

// Separated by a colon, you can pass arbitrary regular expression constraints for your placeholders
Router::get('/books/{id:\d+}', '\Shore\App\Controllers\BooksController@byId');

// Add a new route group. The group URI will be prefixed on all routes within the callback. Infinitely nestable!
Router::group('/authors', function() {
    
    // This route will be available at /authors/search, as it's grouped
    Router::get('/search', '\Shore\App\Controllers\AuthorsController@search');
});
```

## Creating route handlers
Route handlers are callables or controller methods that can handle requests. They can either return the response 
directly (anything _not_ a string will trigger the response to be sent as JSON automatically) or modify the response 
object and return that.

```php
<?php
$handler = function(\Shore\Framework\Http\Request $request, Shore\Framework\Http\Response $response) {
    return $response->withBody('Hello world');
};
```

Requests and responses both implement most of the PSR-7 style messages, except the cloning and stream parts.

## Creating and using middleware
Middlewares are layers any request or response has to pass through. Each middleware can decide whether to send a 
response or pass on to the next one. This allows for neat use cases such as authentication, CSRF or trailing slash 
redirection. Shore has first-class middleware support, in fact, the whole route handling happens inside the 
[Kernel middleware](./lib/Http/Kernel.php).  
Middleware can be supplied as classes implementing the `MiddlewareInterface` or simply callables. To return a response,
they'll need to return the response object. To pass on, they need to call the `$handler->next($request);` method. Easy.

```php
<?php
use \Shore\Framework\Facades\Response;

$middleware = function(\Shore\Framework\Http\Request $request, \Shore\Framework\RequestHandlerInterface $handler) {
    if ($request->get('token')) {
        return $handler->next($request);
    }
    
    return Response::error('Token missing from request', Response::STATUS_UNAUTHORIZED);
};
```

To load middleware into your stack, add the `middleware` key to your application config array and insert middleware 
instances into that:

```php
<?php
return [
    'middleware' => [
        new MyFirstMiddleware(),
        new MySecondMiddleware($withConfiguration)
    ]
];
```

They will be loaded in order of occurrence.

## Dependency injection
At the core of Shore sits the `Application` instance which implements the PSR-11 container interface. It holds all your
services and is set as the context of any route handler. What is a service, you ask? Well, just anything, identified by
a string. You can use that string to retrieve services throughout the lifecycle of your app.  
To stick with the DI paradigm, it's often a good idea to use the class path of an interface as the service ID: 
`DatabaseInterface::class`, for example. Should you switch out the database later on, you'll only need to do so in one 
place, without risking missing it somewhere.

To add your own services, add the `services` key to your application config array and insert your services into that:

```php
<?php
return [
    'services' => [
        'database' => new DatabaseConnection($dbConfig),
        MessageQueueInterface::class => new RedisQueue($redisConfig)
    ]
];
```

They will be registered with the name you passed as the key, so inside any route handler, you can use the following to 
access your service:

```php
    // ...
    $db = $this->get('database'); // Wham! There is your configured instance.
```

## Facades
Maybe you've spotted the `Facade` classes already. These are special classes that allow accessing instance methods on 
your services via static calls, which makes certain aspects easier to handle - without giving up on dependency 
injection. Custom facades only need to implement a single call that returns the name of the service they wish to 
provide, everything else is handled automatically.  
You don't have to use the facades feature, but it will make your life easier. Let's create a fully functional facade for
the database service from the previous section:

```php
<?php

/**
* @method static array query(string $sql)
 */
class Database extends \Shore\Framework\Facade {
    public static function getServiceId(): string {
        return 'database';
    }
}

// Use like:
$results = Database::query('SELECT * from BORING_EXAMPLES');
```

Yup. That's it. Pro tip: Add annotations for your methods in the doc block. That way, your IDE can even autocomplete 
facade methods! Awesome!
