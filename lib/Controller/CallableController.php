<?php

namespace Shore\Framework\Controller;

use Closure;
use Shore\Framework\ControllerInterface;
use Shore\Framework\RequestInterface;
use Shore\Framework\ResponseInterface;

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
     * @param \Shore\Framework\RequestInterface  $request
     * @param \Shore\Framework\ResponseInterface $response
     *
     * @return ResponseInterface|mixed
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        return ($this->callable)->call($this, $request, $response);
    }
}