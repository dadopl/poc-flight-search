<?php

declare(strict_types=1);

namespace App\Flight\Domain\Event;

use App\Shared\Domain\Event\DomainEvent;
use DateTimeImmutable;

final class FlightDelayed extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly DateTimeImmutable $newDepartureTime,
    ) {
        parent::__construct($aggregateId);
    }

    public function getNewDepartureTime(): DateTimeImmutable
    {
        return $this->newDepartureTime;
    }
}
