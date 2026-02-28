<?php

declare(strict_types=1);

namespace App\Flight\Domain\Exception;

use DomainException;

final class FlightNotFoundException extends DomainException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Flight with ID "%s" not found.', $id));
    }

    public static function withFlightNumber(string $flightNumber): self
    {
        return new self(sprintf('Flight with number "%s" not found.', $flightNumber));
    }
}
