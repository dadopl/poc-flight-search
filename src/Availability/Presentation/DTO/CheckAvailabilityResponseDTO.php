<?php

declare(strict_types=1);

namespace App\Availability\Presentation\DTO;

final class CheckAvailabilityResponseDTO
{
    public function __construct(
        public readonly string $flightId,
        public readonly string $flightNumber,
        public readonly string $departureTime,
        public readonly string $arrivalTime,
        public readonly string $cabinClass,
        public readonly int $availableSeats,
        public readonly int $totalSeats,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'flightId'      => $this->flightId,
            'flightNumber'  => $this->flightNumber,
            'departureTime' => $this->departureTime,
            'arrivalTime'   => $this->arrivalTime,
            'cabinClass'    => $this->cabinClass,
            'availableSeats' => $this->availableSeats,
            'totalSeats'    => $this->totalSeats,
        ];
    }
}
