<?php

declare(strict_types=1);

namespace App\Search\Infrastructure\Cache;

use App\Search\Application\Port\SearchResultsCache;
use App\Search\Domain\ValueObject\SearchResultItem;

final class InMemorySearchResultsCache implements SearchResultsCache
{
    private const TTL_SECONDS = 300; // 5 minutes

    /** @var array<string, array{results: SearchResultItem[], storedAt: int}> */
    private array $storage = [];

    /** @param SearchResultItem[] $results */
    public function store(string $sessionId, array $results): void
    {
        $this->storage[$sessionId] = [
            'results'  => $results,
            'storedAt' => time(),
        ];
    }

    /** @return SearchResultItem[]|null */
    public function get(string $sessionId): ?array
    {
        if (!$this->has($sessionId)) {
            return null;
        }

        return $this->storage[$sessionId]['results'];
    }

    public function has(string $sessionId): bool
    {
        if (!isset($this->storage[$sessionId])) {
            return false;
        }

        $storedAt = $this->storage[$sessionId]['storedAt'];

        if ((time() - $storedAt) >= self::TTL_SECONDS) {
            unset($this->storage[$sessionId]);

            return false;
        }

        return true;
    }
}
