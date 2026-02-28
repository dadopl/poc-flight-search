<?php

declare(strict_types=1);

namespace App\Flight\Domain\Event;

use App\Shared\Domain\Event\DomainEvent;

final class FlightDeparted extends DomainEvent
{
    public function __construct(string $aggregateId)
    {
        parent::__construct($aggregateId);
    }
}
