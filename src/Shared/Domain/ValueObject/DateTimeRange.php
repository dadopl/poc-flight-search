<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final class DateTimeRange
{
    public function __construct(
        private readonly DateTimeImmutable $from,
        private readonly DateTimeImmutable $to,
    ) {
        if ($this->to <= $this->from) {
            throw new InvalidArgumentException(
                'DateTimeRange "to" must be strictly after "from".',
            );
        }
    }

    public function getFrom(): DateTimeImmutable
    {
        return $this->from;
    }

    public function getTo(): DateTimeImmutable
    {
        return $this->to;
    }

    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->from && $date <= $this->to;
    }
}
