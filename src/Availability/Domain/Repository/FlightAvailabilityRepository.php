<?php

declare(strict_types=1);

namespace App\Availability\Domain\Repository;

use App\Availability\Domain\Aggregate\FlightAvailability;
use App\Availability\Domain\ValueObject\AvailabilityId;
use App\Availability\Domain\ValueObject\CabinClass;

interface FlightAvailabilityRepository
{
    public function findById(AvailabilityId $id): ?FlightAvailability;

    public function findByFlightAndCabin(string $flightId, CabinClass $cabinClass): ?FlightAvailability;

    /** @return FlightAvailability[] */
    public function findByFlightId(string $flightId): array;

    public function save(FlightAvailability $availability): void;

    public function delete(FlightAvailability $availability): void;
}
