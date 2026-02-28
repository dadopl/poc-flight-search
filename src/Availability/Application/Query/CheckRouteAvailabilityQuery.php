<?php

declare(strict_types=1);

namespace App\Availability\Application\Query;

use App\Shared\Domain\Bus\Query\Query;

final class CheckRouteAvailabilityQuery implements Query
{
    public function __construct(
        public readonly string $departureIata,
        public readonly string $arrivalIata,
        public readonly string $date,
        public readonly int $passengerCount,
        public readonly string $cabinClass,
    ) {
    }
}
