<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Core\ServiceProvider;

use DI\Container;

/**
 * Abstract Service Provider
 *
 * Base class for service providers with common functionality
 *
 * @package Ethileo\EventsPro\Core\ServiceProvider
 */
abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function register(): void;

    /**
     * {@inheritDoc}
     */
    public function boot(): void
    {
        // Default implementation (can be overridden)
    }

    /**
     * Resolve a dependency from the container
     *
     * @template T
     * @param string|class-string<T> $name
     * @return mixed|T
     */
    protected function make(string $name)
    {
        return $this->container->get($name);
    }

    /**
     * Check if a dependency is bound in the container
     *
     * @param string $name
     * @return bool
     */
    protected function has(string $name): bool
    {
        return $this->container->has($name);
    }
}
