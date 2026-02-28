<?php

declare(strict_types=1);

namespace App\Availability\Infrastructure\Persistence;

use App\Availability\Domain\Repository\AirportDailyFlightLimitRepository;
use Doctrine\DBAL\Connection;

final class DoctrineAirportDailyFlightLimitRepository implements AirportDailyFlightLimitRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findDailyLimitByIataCode(string $iataCode): ?int
    {
        $result = $this->connection->fetchOne(
            'SELECT daily_limit FROM airport_daily_flight_limits WHERE iata_code = :iataCode',
            ['iataCode' => strtoupper($iataCode)],
        );

        return $result !== false ? (int) $result : null;
    }

    public function saveDailyLimit(string $iataCode, int $limit): void
    {
        $iataCode = strtoupper($iataCode);

        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM airport_daily_flight_limits WHERE iata_code = :iataCode',
            ['iataCode' => $iataCode],
        );

        if ($exists !== false) {
            $this->connection->update(
                'airport_daily_flight_limits',
                ['daily_limit' => $limit],
                ['iata_code' => $iataCode],
            );
        } else {
            $this->connection->insert(
                'airport_daily_flight_limits',
                ['iata_code' => $iataCode, 'daily_limit' => $limit],
            );
        }
    }
}
