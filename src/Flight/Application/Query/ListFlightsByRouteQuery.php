<?php

declare(strict_types=1);

namespace App\Flight\Application\Query;

use App\Shared\Domain\Bus\Query\Query;

final class ListFlightsByRouteQuery implements Query
{
    public function __construct(
        public readonly string $departureIata,
        public readonly string $arrivalIata,
        public readonly string $date,
    ) {
    }
}
