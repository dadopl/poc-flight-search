<?php

declare(strict_types=1);

namespace App\Search\Domain\ValueObject;

use App\Search\Domain\Exception\InvalidSearchCriteriaException;
use DateTimeImmutable;

final class SearchCriteria
{
    public function __construct(
        private readonly string $departureIata,
        private readonly string $arrivalIata,
        private readonly string $departureDate,
        private readonly ?string $returnDate,
        private readonly int $passengerCount,
        private readonly string $cabinClass,
    ) {
        if ($departureIata === $arrivalIata) {
            throw InvalidSearchCriteriaException::sameAirport($departureIata);
        }

        if ($passengerCount < 1 || $passengerCount > 9) {
            throw InvalidSearchCriteriaException::invalidPassengerCount($passengerCount);
        }

        $today = new DateTimeImmutable('today');
        $departure = new DateTimeImmutable($departureDate);

        if ($departure < $today) {
            throw InvalidSearchCriteriaException::departureDateInThePast($departureDate);
        }
    }

    public function getDepartureIata(): string
    {
        return $this->departureIata;
    }

    public function getArrivalIata(): string
    {
        return $this->arrivalIata;
    }

    public function getDepartureDate(): string
    {
        return $this->departureDate;
    }

    public function getReturnDate(): ?string
    {
        return $this->returnDate;
    }

    public function getPassengerCount(): int
    {
        return $this->passengerCount;
    }

    public function getCabinClass(): string
    {
        return $this->cabinClass;
    }
}
