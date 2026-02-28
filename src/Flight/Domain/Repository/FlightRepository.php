<?php

declare(strict_types=1);

namespace App\Flight\Domain\Repository;

use App\Airport\Domain\AirportId;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\ValueObject\FlightId;
use App\Shared\Domain\ValueObject\DateTimeRange;

interface FlightRepository
{
    public function findById(FlightId $id): ?Flight;

    /**
     * @return Flight[]
     */
    public function findByRoute(AirportId $departure, AirportId $arrival, DateTimeRange $range): array;

    public function save(Flight $flight): void;

    public function delete(Flight $flight): void;
}
