<?php

namespace Shore\Framework\Http;

use Exception;
use Shore\Framework\Application;
use Shore\Framework\Exception\Route\InternalServerErrorException;
use Shore\Framework\Exception\Route\NotFoundException;
use Shore\Framework\Exception\Route\RouteHandlerException;
use Shore\Framework\MiddlewareInterface;
use Shore\Framework\RequestHandlerInterface;
use Shore\Framework\RequestInterface;
use Shore\Framework\ResponseInterface;
use Shore\Framework\RouterInterface;

/**
 * Kernel
 * ======
 *
 * The kernel acts as the main middleware in the stack. It routes requests to handler methods.
 *
 * @package Shore\Framework\Http
 */
class Kernel implements MiddlewareInterface
{
    protected $application;

    protected $router;

    public function __construct(Application $application, RouterInterface $router)
    {
        $this->application = $application;
        $this->router = $router;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Shore\Framework\RequestInterface        $request
     * @param \Shore\Framework\RequestHandlerInterface $handler
     *
     * @return \Shore\Framework\ResponseInterface
     * @throws \Shore\Framework\Exception\Route\InternalServerErrorException
     * @throws \Shore\Framework\Exception\Route\RouteHandlerException
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->run($request, $handler->next($request));
    }

    /**
     * Runs the route
     *
     * @param \Shore\Framework\RequestInterface  $request
     * @param \Shore\Framework\ResponseInterface $response
     *
     * @return \Shore\Framework\ResponseInterface
     * @throws \Shore\Framework\Exception\Route\InternalServerErrorException
     * @throws \Shore\Framework\Exception\Route\RouteHandlerException
     */
    protected function run(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Find a route
        $route = $this->router->match($request);

        // Add the URI arguments to the params
        $request->params()->addArgs($route->getArgs());

        // Bind the handler to the application
        $handler = $route->getHandler();
        $method = $route->getMethod();

        try {
            $output = call_user_func(
                [$handler, $method],
                $request,
                $response,
                ...array_values($route->getArgs())
            );
        } catch (NotFoundException $exception) {
            // TODO: Handle 404s correctly
        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (RouteHandlerException $exception) {
            // The route handler was unable to complete the request and did throw a specific exception to signal what
            // exactly has gone wrong. That is just fine; we'll rethrow at this point and let an error handler further
            // up the stack deal with the problem.
            // The important point is that execution of run() will stop at this point.
            throw $exception;
        } catch (Exception $exception) {
            // Something has gone wrong handling the request, and it didn't throw a specific response exception.
            // We must assume the handler was unable to handle the error itself, so we rethrow as a 500.
            throw new InternalServerErrorException($exception);
        }

        // If the handler didn't return a response instance, we'll set the body of the response to whatever
        // the handler returned. The response itself will take care of format conversion.
        if (! $output instanceof ResponseInterface) {
            /** @var \Shore\Framework\MessageInterface|\Shore\Framework\ResponseInterface $response */
            return $response->withBody($output);
        }

        return $response;
    }
}
