<?php

declare(strict_types=1);

namespace App\Flight\Application\Command;

use App\Shared\Domain\Bus\Command\Command;

final class ScheduleFlightCommand implements Command
{
    public function __construct(
        public readonly string $flightNumber,
        public readonly string $departureAirportIata,
        public readonly string $arrivalAirportIata,
        public readonly string $departureTime,
        public readonly string $arrivalTime,
        public readonly string $aircraftModel,
        public readonly int $aircraftTotalSeats,
    ) {
    }
}
