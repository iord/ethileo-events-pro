<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Infrastructure\Persistence\WordPress;

use Ethileo\EventsPro\Domain\Event\Entity\Event;
use Ethileo\EventsPro\Domain\Event\Repository\EventRepositoryInterface;
use Ethileo\EventsPro\Domain\Shared\ValueObject\Uuid;

/**
 * WordPress Event Repository Implementation
 *
 * @package Ethileo\EventsPro\Infrastructure\Persistence\WordPress
 */
class WordPressEventRepository implements EventRepositoryInterface
{
    private \wpdb $wpdb;
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'ethileo_events';
    }

    public function findById(int $id): ?Event
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        return $row ? Event::fromArray($row) : null;
    }

    public function findByUuid(Uuid $uuid): ?Event
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE uuid = %s", $uuid->toString()),
            ARRAY_A
        );

        return $row ? Event::fromArray($row) : null;
    }

    public function findBySlug(string $slug): ?Event
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE slug = %s", $slug),
            ARRAY_A
        );

        return $row ? Event::fromArray($row) : null;
    }

    public function findByUserId(int $userId, int $limit = 10, int $offset = 0): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE user_id = %d ORDER BY event_date DESC LIMIT %d OFFSET %d",
                $userId,
                $limit,
                $offset
            ),
            ARRAY_A
        );

        return array_map(fn($row) => Event::fromArray($row), $rows);
    }

    public function findByStatus(string $status, int $limit = 10, int $offset = 0): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE status = %s ORDER BY event_date DESC LIMIT %d OFFSET %d",
                $status,
                $limit,
                $offset
            ),
            ARRAY_A
        );

        return array_map(fn($row) => Event::fromArray($row), $rows);
    }

    public function findAll(int $limit = 10, int $offset = 0): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} ORDER BY event_date DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );

        return array_map(fn($row) => Event::fromArray($row), $rows);
    }

    public function save(Event $event): bool
    {
        $data = $event->toArray();
        unset($data['id']); // Remove ID for insert/update logic

        if ($event->getId()) {
            return $this->wpdb->update(
                $this->table,
                $data,
                ['id' => $event->getId()],
                null,
                ['%d']
            ) !== false;
        } else {
            $result = $this->wpdb->insert($this->table, $data);
            if ($result) {
                $event->setId((int)$this->wpdb->insert_id);
                return true;
            }
            return false;
        }
    }

    public function delete(Event $event): bool
    {
        if (!$event->getId()) {
            return false;
        }

        return $this->wpdb->delete(
            $this->table,
            ['id' => $event->getId()],
            ['%d']
        ) !== false;
    }

    public function countByUserId(int $userId): int
    {
        return (int)$this->wpdb->get_var(
            $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d", $userId)
        );
    }

    public function countAll(): int
    {
        return (int)$this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }
}
