<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

interface DomainEventListener
{
    public function handle(DomainEvent $event): void;

    /** @return class-string<DomainEvent> */
    public function subscribedTo(): string;
}
