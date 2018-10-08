<?php

namespace Shore\Framework;

use Shore\Framework\Http\Kernel;
use Shore\Framework\Http\Request;
use Shore\Framework\Http\Response;
use Shore\Framework\Http\Server;
use Shore\Framework\Routing\Router;
use Shore\Framework\Specifications\HttpServerInterface;
use Shore\Framework\Specifications\RequestInterface;
use Shore\Framework\Specifications\ResponseInterface;
use Shore\Framework\Specifications\RouterInterface;

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
     * @param array $configData
     */
    public function __construct(array $configData)
    {
        // Create a config instance
        $config = new Config($configData);

        // Register the configuration
        $this->register('config', $config);

        // Get the error handler in place as early as possible
        $this->bootstrapErrorHandler();

        // Register the hasher
        $this->register(Hash::class, new Hash());

        // Set the timezone
        date_default_timezone_set($config->get('timezone', 'UTC'));

        // Bootstrap all other aspects of the app
        $this->bootstrapFilesystem();
        $this->bootstrapHttpServer();
        $this->bootstrapAppServices();
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
        ?array $server = [],
        ?array $request = [],
        ?array $query = [],
        ?array $body = [],
        ?array $files = [],
        ?array $cookies = [],
        ?array $session = []
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

        /** @var \Shore\Framework\Specifications\HttpServerInterface $server */
        $server = $this->get(HttpServerInterface::class);

        // Run the server
        return $server
            ->run(
                $request,
                function() {
                    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                    return \Shore\Framework\Facades\Response::error('No route handler');
                }
            )
            ->dispatch();
    }

    /**
     * Bootstraps the error handling
     */
    protected function bootstrapErrorHandler(): void
    {
        /** @var \Shore\Framework\Config $config */
        $config = $this->get('config');

        if ($config->has('errors.formatter')) {
            $errorHandler = new ErrorHandler($config->get('errors.formatter'));

            if ($config->has('errors.handler')) {
                $errorHandler->setHandler($config->get('errors.handler'));
            }

            $errorHandler->register();
        }
    }

    /**
     * Bootstraps the filesystem integration
     */
    protected function bootstrapFilesystem(): void
    {
        /** @var \Shore\Framework\Config $config */
        $config = $this->get('config');

        if ($filesystems = $config->get('filesystem', false)) {
            // Register all filesystems by name
            foreach ($filesystems as $name => $filesystem) {
                $this->register("filesystem:$name", $filesystem);
            }

            // Register the first filesystem as the default instance
            $this->register('filesystem', array_shift($filesystems));
        }
    }

    /**
     * Bootstraps the HTTP server stack
     */
    protected function bootstrapHttpServer(): void
    {
        /** @var \Shore\Framework\Config $config */
        $config = $this->get('config');

        // Collect the middleware to load
        $middleware = $config->get('middleware', []);

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

    protected function bootstrapAppServices(): void
    {
        /** @var \Shore\Framework\Config $config */
        $config = $this->get('config');

        $services = $config->get('services', []);

        foreach ($services as $name => $service) {
            $this->register($name, $service);
        }
    }
}
