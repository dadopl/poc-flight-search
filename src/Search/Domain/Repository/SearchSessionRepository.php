<?php

declare(strict_types=1);

namespace App\Search\Domain\Repository;

use App\Search\Domain\Aggregate\SearchSession;
use App\Search\Domain\ValueObject\SearchSessionId;

interface SearchSessionRepository
{
    public function findById(SearchSessionId $id): ?SearchSession;

    public function save(SearchSession $session): void;
}
