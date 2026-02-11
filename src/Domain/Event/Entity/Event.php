<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Domain\Event\Entity;

use Ethileo\EventsPro\Domain\Shared\ValueObject\Uuid;
use DateTimeImmutable;

/**
 * Event Entity
 *
 * Represents a single event in the system
 *
 * @package Ethileo\EventsPro\Domain\Event\Entity
 */
class Event
{
    private ?int $id = null;
    private Uuid $uuid;
    private int $userId;
    private string $title;
    private string $slug;
    private ?string $description;
    private DateTimeImmutable $eventDate;
    private ?DateTimeImmutable $eventEndDate;
    private ?string $location;
    private string $status;
    private array $settings;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    /**
     * Event constructor
     *
     * @param Uuid $uuid
     * @param int $userId
     * @param string $title
     * @param string $slug
     * @param DateTimeImmutable $eventDate
     * @param string $status
     */
    public function __construct(
        Uuid $uuid,
        int $userId,
        string $title,
        string $slug,
        DateTimeImmutable $eventDate,
        string $status = 'draft'
    ) {
        $this->uuid = $uuid;
        $this->userId = $userId;
        $this->title = $title;
        $this->slug = $slug;
        $this->eventDate = $eventDate;
        $this->status = $status;
        $this->settings = [];
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Create a new event
     *
     * @param int $userId
     * @param string $title
     * @param string $slug
     * @param DateTimeImmutable $eventDate
     * @return self
     */
    public static function create(
        int $userId,
        string $title,
        string $slug,
        DateTimeImmutable $eventDate
    ): self {
        return new self(
            Uuid::generate(),
            $userId,
            $title,
            $slug,
            $eventDate
        );
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEventDate(): DateTimeImmutable
    {
        return $this->eventDate;
    }

    public function getEventEndDate(): ?DateTimeImmutable
    {
        return $this->eventEndDate;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Setters with business logic
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function updateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException('Event title cannot be empty');
        }
        $this->title = $title;
        $this->touch();
    }

    public function updateDescription(?string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function updateEventDate(DateTimeImmutable $eventDate): void
    {
        $this->eventDate = $eventDate;
        $this->touch();
    }

    public function setEventEndDate(?DateTimeImmutable $eventEndDate): void
    {
        $this->eventEndDate = $eventEndDate;
        $this->touch();
    }

    public function updateLocation(?string $location): void
    {
        $this->location = $location;
        $this->touch();
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->touch();
    }

    public function draft(): void
    {
        $this->status = 'draft';
        $this->touch();
    }

    public function archive(): void
    {
        $this->status = 'archived';
        $this->touch();
    }

    public function updateSettings(array $settings): void
    {
        $this->settings = array_merge($this->settings, $settings);
        $this->touch();
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isPast(): bool
    {
        return $this->eventDate < new DateTimeImmutable();
    }

    public function isUpcoming(): bool
    {
        return $this->eventDate > new DateTimeImmutable();
    }

    /**
     * Touch the updated timestamp
     */
    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Hydrate from array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $event = new self(
            Uuid::fromString($data['uuid']),
            (int)$data['user_id'],
            $data['title'],
            $data['slug'],
            new DateTimeImmutable($data['event_date']),
            $data['status'] ?? 'draft'
        );

        if (isset($data['id'])) {
            $event->setId((int)$data['id']);
        }

        if (isset($data['description'])) {
            $event->description = $data['description'];
        }

        if (isset($data['event_end_date'])) {
            $event->eventEndDate = new DateTimeImmutable($data['event_end_date']);
        }

        if (isset($data['location'])) {
            $event->location = $data['location'];
        }

        if (isset($data['settings'])) {
            $event->settings = is_array($data['settings'])
                ? $data['settings']
                : json_decode($data['settings'], true);
        }

        if (isset($data['created_at'])) {
            $event->createdAt = new DateTimeImmutable($data['created_at']);
        }

        if (isset($data['updated_at'])) {
            $event->updatedAt = new DateTimeImmutable($data['updated_at']);
        }

        return $event;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid->toString(),
            'user_id' => $this->userId,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'event_date' => $this->eventDate->format('Y-m-d H:i:s'),
            'event_end_date' => $this->eventEndDate?->format('Y-m-d H:i:s'),
            'location' => $this->location,
            'status' => $this->status,
            'settings' => json_encode($this->settings),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
