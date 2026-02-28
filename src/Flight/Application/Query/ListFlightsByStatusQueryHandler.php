<?php

declare(strict_types=1);

namespace App\Flight\Application\Query;

use App\Flight\Application\DTO\FlightResponse;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Repository\FlightRepository;
use App\Flight\Domain\ValueObject\FlightStatus;
use App\Shared\Domain\Bus\Query\QueryHandler;

final class ListFlightsByStatusQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly FlightRepository $flightRepository,
    ) {
    }

    /**
     * @return FlightResponse[]
     */
    public function __invoke(ListFlightsByStatusQuery $query): array
    {
        $status = null;

        if ($query->status !== null) {
            $status = FlightStatus::from(strtoupper($query->status));
        }

        $flights = $this->flightRepository->findByStatus($status, $query->page, $query->limit);

        return array_map(static fn (Flight $f) => new FlightResponse(
            id: $f->getId()->getValue(),
            flightNumber: $f->getFlightNumber()->getValue(),
            departureAirportId: $f->getDepartureAirportId()->getValue(),
            arrivalAirportId: $f->getArrivalAirportId()->getValue(),
            departureTime: $f->getDepartureTime()->format('Y-m-d H:i:s'),
            arrivalTime: $f->getArrivalTime()->format('Y-m-d H:i:s'),
            aircraftModel: $f->getAircraft()->getModel(),
            aircraftTotalSeats: $f->getAircraft()->getTotalSeats(),
            status: $f->getStatus()->value,
        ), $flights);
    }
}
