<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Infrastructure\Persistence\WordPress;

use Ethileo\EventsPro\Domain\Guest\Entity\Guest;
use Ethileo\EventsPro\Domain\Guest\Repository\GuestRepositoryInterface;
use Ethileo\EventsPro\Domain\Shared\ValueObject\Uuid;

/**
 * WordPress Guest Repository Implementation
 *
 * @package Ethileo\EventsPro\Infrastructure\Persistence\WordPress
 */
class WordPressGuestRepository implements GuestRepositoryInterface
{
    private \wpdb $wpdb;
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'ethileo_guests';
    }

    public function findById(int $id): ?Guest
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        return $row ? Guest::fromArray($row) : null;
    }

    public function findByUuid(Uuid $uuid): ?Guest
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE uuid = %s", $uuid->toString()),
            ARRAY_A
        );

        return $row ? Guest::fromArray($row) : null;
    }

    public function findByEventId(int $eventId, int $limit = 100, int $offset = 0): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE event_id = %d ORDER BY first_name ASC LIMIT %d OFFSET %d",
                $eventId,
                $limit,
                $offset
            ),
            ARRAY_A
        );

        return array_map(fn($row) => Guest::fromArray($row), $rows);
    }

    public function findByQrCode(string $qrCode): ?Guest
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE qr_code = %s", $qrCode),
            ARRAY_A
        );

        return $row ? Guest::fromArray($row) : null;
    }

    public function save(Guest $guest): bool
    {
        $data = $guest->toArray();
        unset($data['id']);

        if ($guest->getId()) {
            return $this->wpdb->update(
                $this->table,
                $data,
                ['id' => $guest->getId()],
                null,
                ['%d']
            ) !== false;
        } else {
            $result = $this->wpdb->insert($this->table, $data);
            if ($result) {
                $guest->setId((int)$this->wpdb->insert_id);
                return true;
            }
            return false;
        }
    }

    public function delete(Guest $guest): bool
    {
        if (!$guest->getId()) {
            return false;
        }

        return $this->wpdb->delete(
            $this->table,
            ['id' => $guest->getId()],
            ['%d']
        ) !== false;
    }

    public function countByEventId(int $eventId): int
    {
        return (int)$this->wpdb->get_var(
            $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE event_id = %d", $eventId)
        );
    }

    public function countByRsvpStatus(int $eventId, string $status): int
    {
        return (int)$this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE event_id = %d AND rsvp_status = %s",
                $eventId,
                $status
            )
        );
    }
}
