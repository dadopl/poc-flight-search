<?php

declare(strict_types=1);

namespace App\Search\Application\QueryHandler;

use App\Search\Application\Port\SearchResultsCache;
use App\Search\Application\Query\GetSearchResultsQuery;
use App\Search\Domain\ValueObject\SearchResultItem;
use App\Shared\Domain\Bus\Query\QueryHandler;

final class GetSearchResultsQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly SearchResultsCache $cache,
    ) {
    }

    /**
     * @return array{items: array<array<string, mixed>>, total: int, page: int, perPage: int}
     */
    public function __invoke(GetSearchResultsQuery $query): array
    {
        $allResults = $this->cache->get($query->sessionId) ?? [];

        $total  = count($allResults);
        $offset = ($query->page - 1) * $query->perPage;

        $paginated = array_slice($allResults, $offset, $query->perPage);

        return [
            'items'   => array_map(static fn (SearchResultItem $item) => $item->toArray(), $paginated),
            'total'   => $total,
            'page'    => $query->page,
            'perPage' => $query->perPage,
        ];
    }
}
