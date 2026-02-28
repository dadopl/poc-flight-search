<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus\Query;

use App\Shared\Domain\Bus\Query\Query;
use App\Shared\Domain\Bus\Query\QueryBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class MessengerQueryBus implements QueryBus
{
    public function __construct(
        private readonly MessageBusInterface $queryBus,
    ) {}

    public function ask(Query $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);
        $stamp = $envelope->last(HandledStamp::class);

        return $stamp?->getResult();
    }
}
