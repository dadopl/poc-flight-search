<?php

declare(strict_types=1);

namespace App\Pricing\Application\Query;

use App\Shared\Domain\Bus\Query\Query;

final class GetCurrentPriceQuery implements Query
{
    public function __construct(
        public readonly string $flightId,
        public readonly string $cabinClass,
        public readonly string $departureTime,
        public readonly int $passengerCount,
        public readonly int $availableSeats,
        public readonly int $totalSeats,
    ) {
    }
}
