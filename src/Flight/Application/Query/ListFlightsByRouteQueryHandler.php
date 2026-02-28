<?php

declare(strict_types=1);

namespace App\Flight\Application\Query;

use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\IataCode;
use App\Flight\Application\DTO\FlightResponse;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Repository\FlightRepository;
use App\Shared\Domain\Bus\Query\QueryHandler;
use App\Shared\Domain\ValueObject\DateTimeRange;
use DateTimeImmutable;
use InvalidArgumentException;

final class ListFlightsByRouteQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly FlightRepository $flightRepository,
        private readonly AirportRepository $airportRepository,
    ) {
    }

    /**
     * @return FlightResponse[]
     */
    public function __invoke(ListFlightsByRouteQuery $query): array
    {
        $departureIata = new IataCode($query->departureIata);
        $arrivalIata = new IataCode($query->arrivalIata);

        $departureAirport = $this->airportRepository->findByIataCode($departureIata);
        if ($departureAirport === null) {
            throw new InvalidArgumentException(
                sprintf('Airport with IATA code "%s" not found.', $query->departureIata),
            );
        }

        $arrivalAirport = $this->airportRepository->findByIataCode($arrivalIata);
        if ($arrivalAirport === null) {
            throw new InvalidArgumentException(
                sprintf('Airport with IATA code "%s" not found.', $query->arrivalIata),
            );
        }

        $from = new DateTimeImmutable($query->date . ' 00:00:00');
        $to = new DateTimeImmutable($query->date . ' 23:59:59');
        $range = new DateTimeRange($from, $to);

        $flights = $this->flightRepository->findByRoute(
            $departureAirport->getId(),
            $arrivalAirport->getId(),
            $range,
        );

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
