<?php

declare(strict_types=1);

namespace App\Flight\Domain\Exception;

use App\Flight\Domain\ValueObject\FlightStatus;
use DomainException;

final class InvalidFlightStatusTransitionException extends DomainException
{
    public static function fromTo(FlightStatus $from, FlightStatus $to): self
    {
        return new self(sprintf(
            'Cannot transition flight status from "%s" to "%s".',
            $from->value,
            $to->value,
        ));
    }
}
