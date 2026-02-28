<?php

declare(strict_types=1);

namespace App\Flight\Domain\Exception;

use DomainException;

final class InvalidFlightTimesException extends DomainException
{
    public static function arrivalNotAfterDeparture(): self
    {
        return new self('Arrival time must be strictly after departure time.');
    }

    public static function sameAirport(): self
    {
        return new self('Departure and arrival airports must be different.');
    }
}
