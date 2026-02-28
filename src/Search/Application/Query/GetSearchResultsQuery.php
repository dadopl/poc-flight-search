<?php

declare(strict_types=1);

namespace App\Search\Application\Query;

use App\Shared\Domain\Bus\Query\Query;

final class GetSearchResultsQuery implements Query
{
    public function __construct(
        public readonly string $sessionId,
        public readonly int $page,
        public readonly int $perPage,
    ) {
    }
}
