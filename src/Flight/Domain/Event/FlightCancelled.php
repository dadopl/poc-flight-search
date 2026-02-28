<?php

declare(strict_types=1);

namespace App\Flight\Domain\Event;

use App\Shared\Domain\Event\DomainEvent;

final class FlightCancelled extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $reason,
    ) {
        parent::__construct($aggregateId);
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
