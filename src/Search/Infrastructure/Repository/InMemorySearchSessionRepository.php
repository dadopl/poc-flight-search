<?php

declare(strict_types=1);

namespace App\Search\Infrastructure\Repository;

use App\Search\Domain\Aggregate\SearchSession;
use App\Search\Domain\Repository\SearchSessionRepository;
use App\Search\Domain\ValueObject\SearchSessionId;

final class InMemorySearchSessionRepository implements SearchSessionRepository
{
    /** @var array<string, SearchSession> */
    private array $sessions = [];

    public function findById(SearchSessionId $id): ?SearchSession
    {
        return $this->sessions[$id->getValue()] ?? null;
    }

    public function save(SearchSession $session): void
    {
        $this->sessions[$session->getId()->getValue()] = $session;
    }
}
