<?php

declare(strict_types=1);

namespace App\Airport\Domain\Exception;

use DomainException;

final class AirportNotFoundException extends DomainException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Airport with ID "%s" not found.', $id));
    }

    public static function withIataCode(string $iataCode): self
    {
        return new self(sprintf('Airport with IATA code "%s" not found.', $iataCode));
    }
}
