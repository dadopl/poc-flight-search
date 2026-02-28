<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Event;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Event\DomainEventListener;
use App\Shared\Domain\Event\DomainEventPublisher;

final class SynchronousDomainEventPublisher implements DomainEventPublisher
{
    /** @param iterable<DomainEventListener> $listeners */
    public function __construct(
        private readonly iterable $listeners = [],
    ) {}

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            foreach ($this->listeners as $listener) {
                if ($listener->subscribedTo() === $event::class) {
                    $listener->handle($event);
                }
            }
        }
    }
}
