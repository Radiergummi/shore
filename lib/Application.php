<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 28.09.2018
 * Time: 15:05
 */

namespace Shore\Framework;

use Shore\Framework\Http\Kernel;
use Shore\Framework\Http\Request;
use Shore\Framework\Http\Response;
use Shore\Framework\Http\Server;
use Shore\Framework\Routing\Router;
use Throwable;

/**
 * Application base class. Objects of this type act as a PSR-11 compatible application container that is able to
 * provide services.
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
        // Register the configuration
        $this->register('config', $config);

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
        $server = new Server([$kernel]);

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
        try {
            $request = new Request(
                $this,
                $server,
                $request,
                $query,
                $body,
                $files
            );

            $this->register(RequestInterface::class, $request);

            /** @var \Shore\Framework\HttpServerInterface $server */
            $server = $this->get(HttpServerInterface::class);

            return $server
                ->run(
                    $request,
                    function($request) {
                        return $this
                            ->get(ResponseInterface::class)
                            ->withBody('this is default');
                    }
                )
                ->dispatch();
        } catch (Throwable $exception) {
            // TODO Handle top-level errors
            return $exception->getMessage() . "\n" . $exception->getTraceAsString();
        }
    }
}
