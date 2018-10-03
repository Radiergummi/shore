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

So if you're looking for a small alternative to Laravel, Symfony at al to build your system upon, you've come to the 
right place.

## Getting started
Shore has no fixed layout whatsoever. There are three constants in the [index.php](./public/index.php) that define where
the code for your application is to be found. Just add some routes and build up the layout you desire.

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

## Facades
Maybe you've spotted the `Facade` classes already. These are special classes that allow accessing instance methods on 
your services via static calls, which makes certain aspects easier to handle - without giving up on dependency 
injection. Custom facades only need to implement a single call that returns the name of the service they wish to 
provide, everything else is handled automatically.  
You don't have to use the facades feature, but it will make your life easier.

## Dependency injection
At the core of Shore sits the `Application` instance which implements the PSR-11 container interface. It holds all your
services and is set as the context of any route handler. What is a service, you ask? Well, just anything, identified by
a string. You can use that string to retrieve services throughout the lifecycle of your app.  
To stick with the DI paradigm, it's often a good idea to use the class path of an interface as the service ID: 
`DatabaseInterface::class`, for example. Should you switch out the database later on, you'll only need to do so in one 
place, without risking missing it somewhere.
