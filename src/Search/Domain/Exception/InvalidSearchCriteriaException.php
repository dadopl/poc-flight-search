<?php

declare(strict_types=1);

namespace App\Search\Domain\Exception;

use DomainException;

final class InvalidSearchCriteriaException extends DomainException
{
    public static function departureDateInThePast(string $date): self
    {
        return new self(sprintf('Departure date "%s" cannot be in the past.', $date));
    }

    public static function sameAirport(string $iata): self
    {
        return new self(sprintf('Departure and arrival airports cannot be the same: "%s".', $iata));
    }

    public static function invalidPassengerCount(int $count): self
    {
        return new self(sprintf('Passenger count must be between 1 and 9, got %d.', $count));
    }
}
