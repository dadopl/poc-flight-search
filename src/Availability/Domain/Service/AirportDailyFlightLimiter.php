<?php

declare(strict_types=1);

namespace App\Availability\Domain\Service;

use App\Availability\Domain\Repository\AirportDailyFlightLimitRepository;
use DateTimeImmutable;

final class AirportDailyFlightLimiter
{
    public function __construct(
        private readonly AirportDailyFlightLimitRepository $limitRepository,
    ) {
    }

    public function canAcceptFlight(
        string $airportIataCode,
        DateTimeImmutable $date,
        int $scheduledFlightsCount,
    ): bool {
        $limit = $this->limitRepository->findDailyLimitByIataCode($airportIataCode);

        if ($limit === null) {
            return true;
        }

        return $scheduledFlightsCount < $limit;
    }
}
