<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Domain\Guest\Repository;

use Ethileo\EventsPro\Domain\Guest\Entity\Guest;
use Ethileo\EventsPro\Domain\Shared\ValueObject\Uuid;

/**
 * Guest Repository Interface
 *
 * @package Ethileo\EventsPro\Domain\Guest\Repository
 */
interface GuestRepositoryInterface
{
    public function findById(int $id): ?Guest;
    public function findByUuid(Uuid $uuid): ?Guest;
    public function findByEventId(int $eventId, int $limit = 100, int $offset = 0): array;
    public function findByQrCode(string $qrCode): ?Guest;
    public function save(Guest $guest): bool;
    public function delete(Guest $guest): bool;
    public function countByEventId(int $eventId): int;
    public function countByRsvpStatus(int $eventId, string $status): int;
}
