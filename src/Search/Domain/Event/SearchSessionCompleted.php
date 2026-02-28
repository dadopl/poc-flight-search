<?php

declare(strict_types=1);

namespace App\Search\Domain\Event;

use App\Shared\Domain\Event\DomainEvent;

final class SearchSessionCompleted extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly int $resultCount,
    ) {
        parent::__construct($aggregateId);
    }

    public function getResultCount(): int
    {
        return $this->resultCount;
    }
}
