<?php

declare(strict_types=1);

namespace App\Availability\Application\QueryHandler;

use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\IataCode;
use App\Availability\Application\Query\CheckRouteAvailabilityQuery;
use App\Availability\Domain\Repository\FlightAvailabilityRepository;
use App\Availability\Domain\Service\AirportDailyFlightLimiter;
use App\Availability\Domain\ValueObject\CabinClass;
use App\Availability\Presentation\DTO\CheckAvailabilityResponseDTO;
use App\Flight\Domain\Repository\FlightRepository;
use App\Shared\Domain\Bus\Query\QueryHandler;
use App\Shared\Domain\ValueObject\DateTimeRange;
use DateTimeImmutable;
use InvalidArgumentException;

final class CheckRouteAvailabilityQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly AirportRepository $airportRepository,
        private readonly FlightRepository $flightRepository,
        private readonly FlightAvailabilityRepository $availabilityRepository,
        private readonly AirportDailyFlightLimiter $limiter,
    ) {
    }

    /**
     * @return CheckAvailabilityResponseDTO[]
     */
    public function __invoke(CheckRouteAvailabilityQuery $query): array
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

        $date = new DateTimeImmutable($query->date);

        $scheduledFlightsCount = $this->flightRepository->countByDepartureAirportAndDate(
            $departureAirport->getId(),
            $date,
        );

        if (!$this->limiter->canAcceptFlight($query->departureIata, $date, $scheduledFlightsCount)) {
            return [];
        }

        $range = new DateTimeRange(
            new DateTimeImmutable($query->date . ' 00:00:00'),
            new DateTimeImmutable($query->date . ' 23:59:59'),
        );

        $flights = $this->flightRepository->findByRoute(
            $departureAirport->getId(),
            $arrivalAirport->getId(),
            $range,
        );

        $cabinClass = CabinClass::from($query->cabinClass);
        $result = [];

        foreach ($flights as $flight) {
            $availability = $this->availabilityRepository->findByFlightAndCabin(
                $flight->getId()->getValue(),
                $cabinClass,
            );

            if ($availability === null) {
                continue;
            }

            if ($availability->availableSeats() >= $query->passengerCount) {
                $result[] = new CheckAvailabilityResponseDTO(
                    flightId: $flight->getId()->getValue(),
                    flightNumber: $flight->getFlightNumber()->getValue(),
                    departureTime: $flight->getDepartureTime()->format('Y-m-d H:i:s'),
                    arrivalTime: $flight->getArrivalTime()->format('Y-m-d H:i:s'),
                    cabinClass: $cabinClass->value,
                    availableSeats: $availability->availableSeats(),
                    totalSeats: $availability->getTotalSeats(),
                );
            }
        }

        return $result;
    }
}
