<?php

declare(strict_types=1);

namespace App\Search\Domain\Exception;

use DomainException;

final class InvalidSearchCriteriaException extends DomainException
{
    public static function departureDateInThePast(): self
    {
        return new self('Departure date cannot be in the past.');
    }

    public static function sameDepartureAndArrival(): self
    {
        return new self('Departure and arrival airports must be different.');
    }

    public static function invalidPassengerCount(int $count): self
    {
        return new self(sprintf('Passenger count must be between 1 and 9, got %d.', $count));
    }
}
