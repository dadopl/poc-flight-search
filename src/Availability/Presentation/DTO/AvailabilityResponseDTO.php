<?php

declare(strict_types=1);

namespace App\Availability\Presentation\DTO;

use App\Availability\Domain\Aggregate\FlightAvailability;

final class AvailabilityResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $flightId,
        public readonly string $cabinClass,
        public readonly int $totalSeats,
        public readonly int $bookedSeats,
        public readonly int $blockedSeats,
        public readonly int $availableSeats,
        public readonly int $minimumAvailableThreshold,
        public readonly bool $isAvailable,
    ) {
    }

    public static function fromAggregate(FlightAvailability $availability): self
    {
        return new self(
            id: $availability->getId()->getValue(),
            flightId: $availability->getFlightId(),
            cabinClass: $availability->getCabinClass()->value,
            totalSeats: $availability->getTotalSeats(),
            bookedSeats: $availability->getBookedSeats(),
            blockedSeats: $availability->getBlockedSeats(),
            availableSeats: $availability->availableSeats(),
            minimumAvailableThreshold: $availability->getMinimumAvailableThreshold(),
            isAvailable: $availability->isAvailable(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'                        => $this->id,
            'flightId'                  => $this->flightId,
            'cabinClass'                => $this->cabinClass,
            'totalSeats'                => $this->totalSeats,
            'bookedSeats'               => $this->bookedSeats,
            'blockedSeats'              => $this->blockedSeats,
            'availableSeats'            => $this->availableSeats,
            'minimumAvailableThreshold' => $this->minimumAvailableThreshold,
            'isAvailable'               => $this->isAvailable,
        ];
    }
}
