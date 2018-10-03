<?php

namespace Shore\Framework;

use Shore\Framework\Exception\ServiceMissingException;
use Psr\Container\ContainerInterface;

/**
 * Container
 * =========
 * PSR-11 compatible container implementation. The container provides dependency injection via named services, which
 * makes it possible to stay implementation agnostic and easily swap out services later on, without any hard-wired
 * dependencies across the code.
 * Ideally, services should be identified via the class path of an interface (`MP\Database\ConnectionInterface::class`).
 * For one, anyone using that service relies on it having a stable, public API. Now that service can be swapped with any
 * other that implements the same interface, thereby staying transparently compatible to the previous implementation.
 *
 * @package Shore\Framework
 */
abstract class Container implements ContainerInterface
{
    /**
     * Holds all services the application container provides as a key-value map (service id => service object)
     *
     * @var array
     */
    protected $services;

    /**
     * Registers a service. Services have no restrictions on their type, so you can easily register an array as a
     * service, for example. However, the functionality makes more sense to use for dependency injection - so you should
     * consider passing a config object instead of a plain array to reap all the benefits of DI.
     *
     * @param string $serviceName Name of the service to register. It will be accessible via that key later on.
     * @param mixed  $service     Service data
     *
     * @example $app->register('acmeService', $serviceInstance);
     *          $app->get('acmeService') === $serviceInstance // true
     */
    public function register(string $serviceName, $service): void
    {
        $this->services[$serviceName] = $service;
    }

    /**
     * Registers a service factory. Factories return new instance every time they are requested.
     *
     * @param string $serviceName  Name of the service to register
     * @param string $factoryClass FQCN of the class to create a factory for
     * @param array  $args         List of arguments to pass to the constructor on creation
     *
     * @example $app->factory('acmeService', AcmeService::class, [ 123, 'foo' ]);
     *          $app->get('acmeService'); // equivalent to `new AcmeService(123, 'foo')`
     *          $app->get('acmeService') instanceof AcmeService // true
     *
     */
    public function factory(string $serviceName, string $factoryClass, array $args = []): void
    {
        $this->register(
            $serviceName,
            function() use ($factoryClass, $args) {
                return new $factoryClass(...$args);
            }
        );
    }

    /**
     * Checks whether a service is available
     *
     * @param string $id Identifier of the entry to look for
     *
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->services[$id]);
    }

    /**
     * Checks whether a service is available via magic field name
     *
     * @param $id
     *
     * @return bool
     */
    public function __isset(string $id): bool
    {
        return $this->has($id);
    }

    /**
     * Retrieves a service by name
     *
     * @param string $id Name of the service to retrieve
     *
     * @return mixed
     * @throws \Shore\Framework\Exception\ServiceMissingException if the requested service has not been registered
     */
    public function get($id)
    {
        if (! isset($this->services[$id])) {
            throw new ServiceMissingException("Service $id has not been registered");
        }

        $service = $this->services[$id];

        if (is_callable($service)) {
            return $service();
        }

        return $service;
    }

    /**
     * Retrieves a service by name via magic field
     *
     * @param string $id Name of the service to retrieve
     *
     * @return mixed
     * @throws \Shore\Framework\Exception\ServiceMissingException if the requested service has not been registered
     */
    public function __get(string $id)
    {
        return $this->get($id);
    }
}
