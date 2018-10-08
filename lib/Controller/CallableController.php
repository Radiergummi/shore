<?php

namespace Shore\Framework\Controller;

use Closure;
use Shore\Framework\Specifications\ControllerInterface;
use Shore\Framework\Specifications\RequestInterface;
use Shore\Framework\Specifications\ResponseInterface;

/**
 * Callable controller
 * ===================
 * The callable controller
 *
 * @package Shore\Framework\Controller
 */
class CallableController implements ControllerInterface
{
    /**
     * Holds the controller function
     *
     * @var \Closure
     */
    protected $callable;

    /**
     * CallableController constructor.
     *
     * @param callable $controller
     */
    public function __construct(callable $controller)
    {
        $this->callable = Closure::fromCallable($controller);
    }

    /**
     * Handles the controller call
     *
     * @param \Shore\Framework\Specifications\RequestInterface  $request
     * @param \Shore\Framework\Specifications\ResponseInterface $response
     *
     * @param array                                             $args
     *
     * @return ResponseInterface|mixed
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, ...$args)
    {
        return ($this->callable)->call($this, $request, $response, ...$args);
    }
}
