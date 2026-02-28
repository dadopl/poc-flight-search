<?php

declare(strict_types=1);

namespace App\Airport\Domain\Exception;

use DomainException;

final class InvalidIataCodeException extends DomainException
{
    public static function forCode(string $code): self
    {
        return new self(sprintf(
            'Invalid IATA code "%s". Must be exactly 3 uppercase letters.',
            $code,
        ));
    }
}
