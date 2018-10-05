<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 05.10.2018
 * Time: 15:23
 */

namespace Shore\Framework;

use Psr\Container\ContainerInterface;

/**
 * Container-Aware Interface
 * =========================
 * Classes that implement this interface expect to receive the container instance at some point, allowing access to
 * services on it.
 *
 * @package Shore\Framework
 */
interface ContainerAwareInterface
{
    /**
     * Retrieves the DI container
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * Sets the DI container
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void;
}
