<?php

declare(strict_types=1);

namespace App\Airport\Domain;

use InvalidArgumentException;

final class Country
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $value = strtoupper(trim($value));

        if (!preg_match('/^[A-Z]{2}$/', $value)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid country code "%s". Must be ISO 3166-1 alpha-2 (2 uppercase letters).',
                $value,
            ));
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
