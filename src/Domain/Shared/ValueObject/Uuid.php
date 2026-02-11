<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Domain\Shared\ValueObject;

use Ramsey\Uuid\Uuid as RamseyUuid;

/**
 * UUID Value Object
 *
 * @package Ethileo\EventsPro\Domain\Shared\ValueObject
 */
final class Uuid
{
    private string $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Generate a new UUID
     *
     * @return self
     */
    public static function generate(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    /**
     * Create from string
     *
     * @param string $value
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        if (!RamseyUuid::isValid($value)) {
            throw new \InvalidArgumentException("Invalid UUID: {$value}");
        }

        return new self($value);
    }

    /**
     * Get string value
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Check equality
     *
     * @param Uuid $other
     * @return bool
     */
    public function equals(Uuid $other): bool
    {
        return $this->value === $other->value;
    }
}
