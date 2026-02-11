<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Domain\Guest\Entity;

use Ethileo\EventsPro\Domain\Shared\ValueObject\Uuid;
use Ethileo\EventsPro\Domain\Shared\ValueObject\Email;
use DateTimeImmutable;

/**
 * Guest Entity
 *
 * @package Ethileo\EventsPro\Domain\Guest\Entity
 */
class Guest
{
    private ?int $id = null;
    private Uuid $uuid;
    private int $eventId;
    private string $firstName;
    private ?string $lastName;
    private ?Email $email;
    private ?string $phone;
    private string $rsvpStatus;
    private bool $plusOne;
    private ?string $plusOneName;
    private ?string $dietaryRestrictions;
    private ?string $notes;
    private ?string $qrCode;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        Uuid $uuid,
        int $eventId,
        string $firstName,
        ?string $lastName = null,
        ?Email $email = null
    ) {
        $this->uuid = $uuid;
        $this->eventId = $eventId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->rsvpStatus = 'pending';
        $this->plusOne = false;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        int $eventId,
        string $firstName,
        ?string $lastName = null,
        ?Email $email = null
    ): self {
        return new self(Uuid::generate(), $eventId, $firstName, $lastName, $email);
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUuid(): Uuid { return $this->uuid; }
    public function getEventId(): int { return $this->eventId; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): ?string { return $this->lastName; }
    public function getEmail(): ?Email { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getRsvpStatus(): string { return $this->rsvpStatus; }
    public function hasPlusOne(): bool { return $this->plusOne; }
    public function getPlusOneName(): ?string { return $this->plusOneName; }
    public function getDietaryRestrictions(): ?string { return $this->dietaryRestrictions; }
    public function getNotes(): ?string { return $this->notes; }
    public function getQrCode(): ?string { return $this->qrCode; }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . ($this->lastName ?? ''));
    }

    // Business methods
    public function setId(int $id): void { $this->id = $id; }

    public function acceptRsvp(): void
    {
        $this->rsvpStatus = 'accepted';
        $this->touch();
    }

    public function declineRsvp(): void
    {
        $this->rsvpStatus = 'declined';
        $this->touch();
    }

    public function setPending(): void
    {
        $this->rsvpStatus = 'pending';
        $this->touch();
    }

    public function enablePlusOne(string $name = null): void
    {
        $this->plusOne = true;
        $this->plusOneName = $name;
        $this->touch();
    }

    public function disablePlusOne(): void
    {
        $this->plusOne = false;
        $this->plusOneName = null;
        $this->touch();
    }

    public function updateContactInfo(?Email $email, ?string $phone): void
    {
        $this->email = $email;
        $this->phone = $phone;
        $this->touch();
    }

    public function setQrCode(string $qrCode): void
    {
        $this->qrCode = $qrCode;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function fromArray(array $data): self
    {
        $guest = new self(
            Uuid::fromString($data['uuid']),
            (int)$data['event_id'],
            $data['first_name'],
            $data['last_name'] ?? null,
            isset($data['email']) ? Email::fromString($data['email']) : null
        );

        if (isset($data['id'])) $guest->setId((int)$data['id']);
        if (isset($data['phone'])) $guest->phone = $data['phone'];
        if (isset($data['rsvp_status'])) $guest->rsvpStatus = $data['rsvp_status'];
        if (isset($data['plus_one'])) $guest->plusOne = (bool)$data['plus_one'];
        if (isset($data['plus_one_name'])) $guest->plusOneName = $data['plus_one_name'];
        if (isset($data['dietary_restrictions'])) $guest->dietaryRestrictions = $data['dietary_restrictions'];
        if (isset($data['notes'])) $guest->notes = $data['notes'];
        if (isset($data['qr_code'])) $guest->qrCode = $data['qr_code'];

        return $guest;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid->toString(),
            'event_id' => $this->eventId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email?->toString(),
            'phone' => $this->phone,
            'rsvp_status' => $this->rsvpStatus,
            'plus_one' => $this->plusOne ? 1 : 0,
            'plus_one_name' => $this->plusOneName,
            'dietary_restrictions' => $this->dietaryRestrictions,
            'notes' => $this->notes,
            'qr_code' => $this->qrCode,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
