<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Domain\Event\Repository;

use Ethileo\EventsPro\Domain\Event\Entity\Event;
use Ethileo\EventsPro\Domain\Shared\ValueObject\Uuid;

/**
 * Event Repository Interface
 *
 * @package Ethileo\EventsPro\Domain\Event\Repository
 */
interface EventRepositoryInterface
{
    /**
     * Find event by ID
     *
     * @param int $id
     * @return Event|null
     */
    public function findById(int $id): ?Event;

    /**
     * Find event by UUID
     *
     * @param Uuid $uuid
     * @return Event|null
     */
    public function findByUuid(Uuid $uuid): ?Event;

    /**
     * Find event by slug
     *
     * @param string $slug
     * @return Event|null
     */
    public function findBySlug(string $slug): ?Event;

    /**
     * Find all events for a user
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return Event[]
     */
    public function findByUserId(int $userId, int $limit = 10, int $offset = 0): array;

    /**
     * Find events by status
     *
     * @param string $status
     * @param int $limit
     * @param int $offset
     * @return Event[]
     */
    public function findByStatus(string $status, int $limit = 10, int $offset = 0): array;

    /**
     * Get all events
     *
     * @param int $limit
     * @param int $offset
     * @return Event[]
     */
    public function findAll(int $limit = 10, int $offset = 0): array;

    /**
     * Save event
     *
     * @param Event $event
     * @return bool
     */
    public function save(Event $event): bool;

    /**
     * Delete event
     *
     * @param Event $event
     * @return bool
     */
    public function delete(Event $event): bool;

    /**
     * Count events by user
     *
     * @param int $userId
     * @return int
     */
    public function countByUserId(int $userId): int;

    /**
     * Count total events
     *
     * @return int
     */
    public function countAll(): int;
}
