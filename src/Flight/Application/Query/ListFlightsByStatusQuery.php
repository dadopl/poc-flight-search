<?php

declare(strict_types=1);

namespace App\Flight\Application\Query;

use App\Shared\Domain\Bus\Query\Query;

final class ListFlightsByStatusQuery implements Query
{
    public function __construct(
        public readonly ?string $status,
        public readonly int $page = 1,
        public readonly int $limit = 20,
    ) {
    }
}
