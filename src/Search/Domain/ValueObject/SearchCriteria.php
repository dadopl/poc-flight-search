<?php

declare(strict_types=1);

namespace App\Search\Domain\ValueObject;

use App\Search\Domain\Exception\InvalidSearchCriteriaException;
use DateTimeImmutable;
use InvalidArgumentException;

final class SearchCriteria
{
    public function __construct(
        private readonly string $departureIata,
        private readonly string $arrivalIata,
        private readonly DateTimeImmutable $departureDate,
        private readonly ?DateTimeImmutable $returnDate,
        private readonly int $passengerCount,
        private readonly CabinClass $cabinClass,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (!preg_match('/^[A-Z]{3}$/', $this->departureIata)) {
            throw new InvalidArgumentException(sprintf('Invalid IATA code: "%s"', $this->departureIata));
        }

        if (!preg_match('/^[A-Z]{3}$/', $this->arrivalIata)) {
            throw new InvalidArgumentException(sprintf('Invalid IATA code: "%s"', $this->arrivalIata));
        }

        if ($this->departureIata === $this->arrivalIata) {
            throw InvalidSearchCriteriaException::sameDepartureAndArrival();
        }

        $today = new DateTimeImmutable('today');
        if ($this->departureDate < $today) {
            throw InvalidSearchCriteriaException::departureDateInThePast();
        }

        if ($this->passengerCount < 1 || $this->passengerCount > 9) {
            throw InvalidSearchCriteriaException::invalidPassengerCount($this->passengerCount);
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

    public function getDepartureDate(): DateTimeImmutable
    {
        return $this->departureDate;
    }

    public function getReturnDate(): ?DateTimeImmutable
    {
        return $this->returnDate;
    }

    public function getPassengerCount(): int
    {
        return $this->passengerCount;
    }

    public function getCabinClass(): CabinClass
    {
        return $this->cabinClass;
    }
}
