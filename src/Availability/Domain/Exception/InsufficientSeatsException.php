<?php

declare(strict_types=1);

namespace App\Availability\Domain\Exception;

use DomainException;

final class InsufficientSeatsException extends DomainException
{
    public static function forBooking(int $requested, int $available): self
    {
        return new self(sprintf(
            'Cannot book %d seat(s): only %d available.',
            $requested,
            $available,
        ));
    }

    public static function forBlocking(int $requested, int $available): self
    {
        return new self(sprintf(
            'Cannot block %d seat(s): only %d available.',
            $requested,
            $available,
        ));
    }
}
