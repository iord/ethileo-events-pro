<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Core\ServiceProvider;

/**
 * Service Provider Interface
 *
 * All service providers must implement this interface
 *
 * @package Ethileo\EventsPro\Core\ServiceProvider
 */
interface ServiceProviderInterface
{
    /**
     * Register services in the container
     *
     * @return void
     */
    public function register(): void;

    /**
     * Boot the service provider
     *
     * Called after all providers have been registered
     *
     * @return void
     */
    public function boot(): void;
}
