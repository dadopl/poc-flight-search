<?php

declare(strict_types=1);

namespace App\Flight\Domain\ValueObject;

use App\Flight\Domain\Exception\InvalidFlightNumberException;

final class FlightNumber
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $value = strtoupper(trim($value));

        if (!preg_match('/^[A-Z]{2}\d{1,4}$/', $value)) {
            throw InvalidFlightNumberException::forNumber($value);
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
