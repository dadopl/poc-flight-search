<?php

declare(strict_types=1);

namespace App\Flight\Domain\Event;

use App\Shared\Domain\Event\DomainEvent;
use DateTimeImmutable;

final class FlightScheduled extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $flightNumber,
        private readonly string $departureAirportId,
        private readonly string $arrivalAirportId,
        private readonly DateTimeImmutable $departureTime,
        private readonly DateTimeImmutable $arrivalTime,
    ) {
        parent::__construct($aggregateId);
    }

    public function getFlightNumber(): string
    {
        return $this->flightNumber;
    }

    public function getDepartureAirportId(): string
    {
        return $this->departureAirportId;
    }

    public function getArrivalAirportId(): string
    {
        return $this->arrivalAirportId;
    }

    public function getDepartureTime(): DateTimeImmutable
    {
        return $this->departureTime;
    }

    public function getArrivalTime(): DateTimeImmutable
    {
        return $this->arrivalTime;
    }
}
