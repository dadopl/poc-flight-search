<?php

declare(strict_types=1);

namespace App\Flight\Application\Command;

use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\Exception\AirportNotFoundException;
use App\Airport\Domain\IataCode;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Repository\FlightRepository;
use App\Flight\Domain\ValueObject\Aircraft;
use App\Flight\Domain\ValueObject\FlightId;
use App\Flight\Domain\ValueObject\FlightNumber;
use App\Shared\Domain\Bus\Command\CommandHandler;
use DateTimeImmutable;

final class ScheduleFlightCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly FlightRepository $flightRepository,
        private readonly AirportRepository $airportRepository,
    ) {
    }

    public function __invoke(ScheduleFlightCommand $command): void
    {
        $departureIata = new IataCode($command->departureAirportIata);
        $arrivalIata = new IataCode($command->arrivalAirportIata);

        $departureAirport = $this->airportRepository->findByIataCode($departureIata);
        if ($departureAirport === null) {
            throw AirportNotFoundException::withIataCode($command->departureAirportIata);
        }

        $arrivalAirport = $this->airportRepository->findByIataCode($arrivalIata);
        if ($arrivalAirport === null) {
            throw AirportNotFoundException::withIataCode($command->arrivalAirportIata);
        }

        $flight = Flight::schedule(
            FlightId::generate(),
            new FlightNumber($command->flightNumber),
            $departureAirport->getId(),
            $arrivalAirport->getId(),
            new DateTimeImmutable($command->departureTime),
            new DateTimeImmutable($command->arrivalTime),
            new Aircraft($command->aircraftModel, $command->aircraftTotalSeats),
        );

        $this->flightRepository->save($flight);
    }
}
