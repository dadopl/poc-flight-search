<?php

declare(strict_types=1);

namespace App\Flight\Domain\Exception;

use DomainException;

final class InvalidFlightNumberException extends DomainException
{
    public static function forNumber(string $number): self
    {
        return new self(sprintf(
            'Invalid flight number "%s". Must be 2 uppercase letters followed by 1 to 4 digits (e.g. LO123, FR4567).',
            $number,
        ));
    }
}
