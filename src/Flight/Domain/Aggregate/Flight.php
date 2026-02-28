<?php

declare(strict_types=1);

namespace App\Flight\Domain\Aggregate;

use App\Airport\Domain\AirportId;
use App\Flight\Domain\Event\FlightArrived;
use App\Flight\Domain\Event\FlightBoardingStarted;
use App\Flight\Domain\Event\FlightCancelled;
use App\Flight\Domain\Event\FlightDelayed;
use App\Flight\Domain\Event\FlightDeparted;
use App\Flight\Domain\Event\FlightScheduled;
use App\Flight\Domain\Exception\InvalidFlightTimesException;
use App\Flight\Domain\Service\FlightStatusTransitionPolicy;
use App\Flight\Domain\ValueObject\Aircraft;
use App\Flight\Domain\ValueObject\FlightId;
use App\Flight\Domain\ValueObject\FlightNumber;
use App\Flight\Domain\ValueObject\FlightStatus;
use App\Shared\Domain\AggregateRoot;
use DateTimeImmutable;
use InvalidArgumentException;

final class Flight extends AggregateRoot
{
    private FlightStatus $status;

    private function __construct(
        private readonly FlightId $id,
        private readonly FlightNumber $flightNumber,
        private readonly AirportId $departureAirportId,
        private readonly AirportId $arrivalAirportId,
        private DateTimeImmutable $departureTime,
        private readonly DateTimeImmutable $arrivalTime,
        private readonly Aircraft $aircraft,
    ) {
        $this->status = FlightStatus::SCHEDULED;
    }

    public static function schedule(
        FlightId $id,
        FlightNumber $flightNumber,
        AirportId $departureAirportId,
        AirportId $arrivalAirportId,
        DateTimeImmutable $departureTime,
        DateTimeImmutable $arrivalTime,
        Aircraft $aircraft,
    ): self {
        if ($departureAirportId->equals($arrivalAirportId)) {
            throw InvalidFlightTimesException::sameAirport();
        }

        if ($arrivalTime <= $departureTime) {
            throw InvalidFlightTimesException::arrivalNotAfterDeparture();
        }

        $flight = new self(
            $id,
            $flightNumber,
            $departureAirportId,
            $arrivalAirportId,
            $departureTime,
            $arrivalTime,
            $aircraft,
        );

        $flight->recordEvent(new FlightScheduled(
            $id->getValue(),
            $flightNumber->getValue(),
            $departureAirportId->getValue(),
            $arrivalAirportId->getValue(),
            $departureTime,
            $arrivalTime,
        ));

        return $flight;
    }

    public static function fromPrimitives(
        string $id,
        string $flightNumber,
        string $departureAirportId,
        string $arrivalAirportId,
        DateTimeImmutable $departureTime,
        DateTimeImmutable $arrivalTime,
        string $aircraftModel,
        int $aircraftTotalSeats,
        string $status,
    ): self {
        $flight = new self(
            new FlightId($id),
            new FlightNumber($flightNumber),
            new AirportId($departureAirportId),
            new AirportId($arrivalAirportId),
            $departureTime,
            $arrivalTime,
            new Aircraft($aircraftModel, $aircraftTotalSeats),
        );
        $flight->status = FlightStatus::from($status);

        return $flight;
    }

    public function delay(DateTimeImmutable $newDepartureTime): void
    {
        FlightStatusTransitionPolicy::assertCanTransition($this->status, FlightStatus::DELAYED);

        if ($newDepartureTime >= $this->arrivalTime) {
            throw new InvalidArgumentException(
                'New departure time must be before arrival time.',
            );
        }

        $this->departureTime = $newDepartureTime;
        $this->status = FlightStatus::DELAYED;
        $this->recordEvent(new FlightDelayed($this->id->getValue(), $newDepartureTime));
    }

    public function cancel(string $reason): void
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('Cancellation reason cannot be empty.');
        }

        FlightStatusTransitionPolicy::assertCanTransition($this->status, FlightStatus::CANCELLED);

        $this->status = FlightStatus::CANCELLED;
        $this->recordEvent(new FlightCancelled($this->id->getValue(), $reason));
    }

    public function board(): void
    {
        FlightStatusTransitionPolicy::assertCanTransition($this->status, FlightStatus::BOARDING);

        $this->status = FlightStatus::BOARDING;
        $this->recordEvent(new FlightBoardingStarted($this->id->getValue()));
    }

    public function depart(): void
    {
        FlightStatusTransitionPolicy::assertCanTransition($this->status, FlightStatus::DEPARTED);

        $this->status = FlightStatus::DEPARTED;
        $this->recordEvent(new FlightDeparted($this->id->getValue()));
    }

    public function arrive(): void
    {
        FlightStatusTransitionPolicy::assertCanTransition($this->status, FlightStatus::ARRIVED);

        $this->status = FlightStatus::ARRIVED;
        $this->recordEvent(new FlightArrived($this->id->getValue()));
    }

    public function getId(): FlightId
    {
        return $this->id;
    }

    public function getFlightNumber(): FlightNumber
    {
        return $this->flightNumber;
    }

    public function getDepartureAirportId(): AirportId
    {
        return $this->departureAirportId;
    }

    public function getArrivalAirportId(): AirportId
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

    public function getAircraft(): Aircraft
    {
        return $this->aircraft;
    }

    public function getStatus(): FlightStatus
    {
        return $this->status;
    }
}
