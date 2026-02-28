<?php

declare(strict_types=1);

namespace App\Flight\Application\DTO;

final class FlightResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $flightNumber,
        public readonly string $departureAirportId,
        public readonly string $arrivalAirportId,
        public readonly string $departureTime,
        public readonly string $arrivalTime,
        public readonly string $aircraftModel,
        public readonly int $aircraftTotalSeats,
        public readonly string $status,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'flightNumber'       => $this->flightNumber,
            'departureAirportId' => $this->departureAirportId,
            'arrivalAirportId'   => $this->arrivalAirportId,
            'departureTime'      => $this->departureTime,
            'arrivalTime'        => $this->arrivalTime,
            'aircraftModel'      => $this->aircraftModel,
            'aircraftTotalSeats' => $this->aircraftTotalSeats,
            'status'             => $this->status,
        ];
    }
}
