<?php

declare(strict_types=1);

namespace App\Availability\Domain\Repository;

interface AirportDailyFlightLimitRepository
{
    public function findDailyLimitByIataCode(string $iataCode): ?int;

    public function saveDailyLimit(string $iataCode, int $limit): void;
}
