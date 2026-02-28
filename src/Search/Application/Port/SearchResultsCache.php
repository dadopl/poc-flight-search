<?php

declare(strict_types=1);

namespace App\Search\Application\Port;

use App\Search\Domain\ValueObject\SearchResultItem;

interface SearchResultsCache
{
    /** @param SearchResultItem[] $results */
    public function store(string $sessionId, array $results): void;

    /** @return SearchResultItem[]|null */
    public function get(string $sessionId): ?array;

    public function has(string $sessionId): bool;
}
