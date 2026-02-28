<?php

declare(strict_types=1);

namespace App\Airport\Domain;

use InvalidArgumentException;

final class AirportName
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Airport name cannot be empty.');
        }

        if (mb_strlen($value) > 100) {
            throw new InvalidArgumentException('Airport name must not exceed 100 characters.');
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
