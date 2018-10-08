<?php

namespace Shore\Framework;

use Shore\Framework\Http\Kernel;
use Shore\Framework\Http\Request;
use Shore\Framework\Http\Response;
use Shore\Framework\Http\Server;
use Shore\Framework\Routing\Router;

/**
 * Application
 * ===========
 * The core of a shore application. It's primary job is handling the request and creating a string response, plus wiring
 * all the core stuff together in the constructor.
 *
 * Of course, you can also roll your own application. Objects of this type act as a PSR-11 compatible application
 * container that is able to provide services.
 *
 * From here on, exploration can take multiple paths. To learn more about the middleware stack, proceed to
 * lib/Http/Server.php. For the router, go to lib/Routing/Router.php, and to see the actual request handling, take a
 * look at lib/Http/Kernel.php.
 *
 * @package Shore\Framework
 */
class Application extends Container
{
    /**
     * Initializes the application. This is the place to attach core services and configure it.
     *
     * @param $config
     */
    public function __construct($config)
    {
        if (
            array_key_exists('errors', $config) &&
            array_key_exists('formatter', $config['errors'])
        ) {
            $errorHandler = new ErrorHandler($config['errors']['formatter']);

            if (array_key_exists('handler', $config['errors'])) {
                $errorHandler->setHandler($config['errors']['handler']);
            }

            $errorHandler->register();
        }

        // Register the configuration
        $this->register('config', $config);

        // Collect the middleware to load
        $middleware = array_key_exists('middleware', $config) ? $config['middleware'] : [];

        // Register the router using the router interface as the service ID. This allows to swap the router
        // for another implementation later in the product lifecycle, as long as it implements the interface.
        $this->register(RouterInterface::class, new Router());

        // Register the response factory
        $this->factory(ResponseInterface::class, Response::class);

        // Create a new kernel instance. The kernel is the main middleware used to handle and respond to incoming
        // requests, so it should be loaded as the last middleware in the stack.
        $kernel = new Kernel($this, $this->get(RouterInterface::class));

        // Create a new server instance. The server runs through all middleware layers, passing request and response
        // between them.
        $server = new Server(array_merge($middleware, [$kernel]));

        // Register the server
        $this->register(HttpServerInterface::class, $server);
    }

    /**
     * Runs the application. All request-based global arrays can be monkey-patched to enable easy mocks while testing.
     *
     * @param array $server
     * @param array $request
     * @param array $query
     * @param array $body
     * @param array $files
     * @param array $cookies
     * @param array $session
     *
     * @return string
     * @throws \Throwable
     */
    public function run(
        $server = [],
        $request = [],
        $query = [],
        $body = [],
        $files = [],
        $cookies = [],
        $session = []
    ): string {
        $request = func_num_args() === 0
            ? Request::fromGlobals()
            : new Request(
                $server,
                $request,
                $query,
                $body,
                $files
            );

        // Keep the request object in the container
        $this->register(RequestInterface::class, $request);

        /** @var \Shore\Framework\HttpServerInterface $server */
        $server = $this->get(HttpServerInterface::class);

        // Run the server
        return $server
            ->run(
                $request,
                function($request) {
                    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                    return \Shore\Framework\Facades\Response::error('No route handler');
                }
            )
            ->dispatch();
    }
}
