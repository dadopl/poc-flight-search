<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

use DateTimeImmutable;

abstract class DomainEvent
{
    private readonly string $eventId;
    private readonly DateTimeImmutable $occurredAt;
    private readonly string $aggregateId;

    public function __construct(string $aggregateId)
    {
        $this->aggregateId = $aggregateId;
        $this->eventId = bin2hex(random_bytes(16));
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }
}
