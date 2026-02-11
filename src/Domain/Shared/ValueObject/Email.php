<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Domain\Shared\ValueObject;

/**
 * Email Value Object
 *
 * @package Ethileo\EventsPro\Domain\Shared\ValueObject
 */
final class Email
{
    private string $value;

    /**
     * @param string $value
     * @throws \InvalidArgumentException
     */
    private function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address: {$value}");
        }

        $this->value = strtolower($value);
    }

    /**
     * Create from string
     *
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): self
    {
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
     * @param Email $other
     * @return bool
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
