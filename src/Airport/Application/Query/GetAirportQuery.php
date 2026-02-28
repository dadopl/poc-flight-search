<?php

declare(strict_types=1);

namespace App\Airport\Application\Query;

use App\Shared\Domain\Bus\Query\Query;

final class GetAirportQuery implements Query
{
    public function __construct(
        public readonly string $iataCode,
    ) {
    }
}
