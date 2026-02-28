<?php

declare(strict_types=1);

namespace App\Flight\Application\Query;

use App\Flight\Application\DTO\FlightResponse;
use App\Flight\Domain\Exception\FlightNotFoundException;
use App\Flight\Domain\Repository\FlightRepository;
use App\Flight\Domain\ValueObject\FlightNumber;
use App\Shared\Domain\Bus\Query\QueryHandler;

final class GetFlightQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly FlightRepository $flightRepository,
    ) {
    }

    public function __invoke(GetFlightQuery $query): FlightResponse
    {
        $flightNumber = new FlightNumber($query->flightNumber);
        $flight = $this->flightRepository->findByFlightNumber($flightNumber);

        if ($flight === null) {
            throw FlightNotFoundException::withFlightNumber($query->flightNumber);
        }

        return new FlightResponse(
            id: $flight->getId()->getValue(),
            flightNumber: $flight->getFlightNumber()->getValue(),
            departureAirportId: $flight->getDepartureAirportId()->getValue(),
            arrivalAirportId: $flight->getArrivalAirportId()->getValue(),
            departureTime: $flight->getDepartureTime()->format('Y-m-d H:i:s'),
            arrivalTime: $flight->getArrivalTime()->format('Y-m-d H:i:s'),
            aircraftModel: $flight->getAircraft()->getModel(),
            aircraftTotalSeats: $flight->getAircraft()->getTotalSeats(),
            status: $flight->getStatus()->value,
        );
    }
}
