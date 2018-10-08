<?php

namespace Shore\Framework\Controller;

use Psr\Container\ContainerInterface;
use Shore\Framework\Specifications\ContainerAwareInterface;
use Shore\Framework\Specifications\ControllerInterface;

/**
 * Base controller
 * ===============
 * This is the base class all controllers need to extend. It provides DI container integration.
 *
 * @package Shore\Framework\Controller
 */
class Controller implements ControllerInterface, ContainerAwareInterface
{
    /**
     * Holds the container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Retrieves a service from the application container
     *
     * @param string $id
     *
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->container->get($id);
    }

    /**
     * Retrieves the DI container
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        // TODO: Implement getContainer() method.
    }

    /**
     * Sets the DI container
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        // TODO: Implement setContainer() method.
    }
}
