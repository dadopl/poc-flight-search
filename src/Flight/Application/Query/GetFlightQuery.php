<?php

declare(strict_types=1);

namespace App\Flight\Application\Query;

use App\Shared\Domain\Bus\Query\Query;

final class GetFlightQuery implements Query
{
    public function __construct(
        public readonly string $flightNumber,
    ) {
    }
}
