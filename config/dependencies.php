<?php

declare(strict_types=1);

/**
 * Dependency Injection Container Configuration
 *
 * @package Ethileo\EventsPro
 */

use Ethileo\EventsPro\Domain\Event\Repository\EventRepositoryInterface;
use Ethileo\EventsPro\Domain\Guest\Repository\GuestRepositoryInterface;
use Ethileo\EventsPro\Infrastructure\Persistence\WordPress\WordPressEventRepository;
use Ethileo\EventsPro\Infrastructure\Persistence\WordPress\WordPressGuestRepository;

return [
    // Repository bindings
    EventRepositoryInterface::class => \DI\create(WordPressEventRepository::class),
    GuestRepositoryInterface::class => \DI\create(WordPressGuestRepository::class),
];
