<?php

declare(strict_types=1);

namespace App\Search\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class SearchResultItem
{
    public function __construct(
        private readonly string $flightId,
        private readonly string $flightNumber,
        private readonly string $departureIata,
        private readonly string $arrivalIata,
        private readonly DateTimeImmutable $departureTime,
        private readonly DateTimeImmutable $arrivalTime,
        private readonly int $availableSeats,
        private readonly CabinClass $cabinClass,
        private readonly Money $basePrice,
    ) {
    }

    public function getFlightId(): string
    {
        return $this->flightId;
    }

    public function getFlightNumber(): string
    {
        return $this->flightNumber;
    }

    public function getDepartureIata(): string
    {
        return $this->departureIata;
    }

    public function getArrivalIata(): string
    {
        return $this->arrivalIata;
    }

    public function getDepartureTime(): DateTimeImmutable
    {
        return $this->departureTime;
    }

    public function getArrivalTime(): DateTimeImmutable
    {
        return $this->arrivalTime;
    }

    public function getAvailableSeats(): int
    {
        return $this->availableSeats;
    }

    public function getCabinClass(): CabinClass
    {
        return $this->cabinClass;
    }

    public function getBasePrice(): Money
    {
        return $this->basePrice;
    }
}
