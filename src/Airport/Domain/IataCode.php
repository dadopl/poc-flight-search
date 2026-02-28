<?php

declare(strict_types=1);

namespace App\Airport\Domain;

use InvalidArgumentException;

final class IataCode
{
    private string $value;

    public function __construct(string $value)
    {
        $value = strtoupper(trim($value));

        if (!preg_match('/^[A-Z]{3}$/', $value)) {
            throw new InvalidArgumentException(
                sprintf('Invalid IATA code "%s". Must be exactly 3 uppercase letters.', $value)
            );
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
