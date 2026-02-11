<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Infrastructure\ServiceProvider;

use Ethileo\EventsPro\Core\ServiceProvider\AbstractServiceProvider;
use Ethileo\EventsPro\Domain\Event\Repository\EventRepositoryInterface;
use Ethileo\EventsPro\Domain\Guest\Repository\GuestRepositoryInterface;
use Ethileo\EventsPro\Infrastructure\Persistence\WordPress\WordPressEventRepository;
use Ethileo\EventsPro\Infrastructure\Persistence\WordPress\WordPressGuestRepository;

/**
 * Database Service Provider
 *
 * @package Ethileo\EventsPro\Infrastructure\ServiceProvider
 */
class DatabaseServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->container->set(EventRepositoryInterface::class, \DI\create(WordPressEventRepository::class));
        $this->container->set(GuestRepositoryInterface::class, \DI\create(WordPressGuestRepository::class));
    }
}
