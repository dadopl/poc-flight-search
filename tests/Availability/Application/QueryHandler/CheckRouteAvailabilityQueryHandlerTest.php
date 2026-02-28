<?php

declare(strict_types=1);

namespace App\Tests\Availability\Application\QueryHandler;

use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportId;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\IataCode;
use App\Availability\Application\Query\CheckRouteAvailabilityQuery;
use App\Availability\Application\QueryHandler\CheckRouteAvailabilityQueryHandler;
use App\Availability\Domain\Aggregate\FlightAvailability;
use App\Availability\Domain\Repository\AirportDailyFlightLimitRepository;
use App\Availability\Domain\Repository\FlightAvailabilityRepository;
use App\Availability\Domain\Service\AirportDailyFlightLimiter;
use App\Availability\Domain\ValueObject\AvailabilityId;
use App\Availability\Domain\ValueObject\CabinClass;
use App\Availability\Presentation\DTO\CheckAvailabilityResponseDTO;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Repository\FlightRepository;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckRouteAvailabilityQueryHandlerTest extends TestCase
{
    private AirportRepository&MockObject $airportRepository;
    private FlightRepository&MockObject $flightRepository;
    private FlightAvailabilityRepository&MockObject $availabilityRepository;
    private AirportDailyFlightLimitRepository&MockObject $limitRepository;
    private CheckRouteAvailabilityQueryHandler $handler;

    private const DEPARTURE_ID = '550e8400-e29b-41d4-a716-446655440001';
    private const ARRIVAL_ID   = '550e8400-e29b-41d4-a716-446655440002';
    private const FLIGHT_ID    = '550e8400-e29b-41d4-a716-446655440010';

    protected function setUp(): void
    {
        $this->airportRepository    = $this->createMock(AirportRepository::class);
        $this->flightRepository     = $this->createMock(FlightRepository::class);
        $this->availabilityRepository = $this->createMock(FlightAvailabilityRepository::class);
        $this->limitRepository      = $this->createMock(AirportDailyFlightLimitRepository::class);

        $limiter = new AirportDailyFlightLimiter($this->limitRepository);

        $this->handler = new CheckRouteAvailabilityQueryHandler(
            $this->airportRepository,
            $this->flightRepository,
            $this->availabilityRepository,
            $limiter,
        );
    }

    private function makeAirport(string $id, string $iata): Airport
    {
        return Airport::fromPrimitives($id, $iata, 'Airport', 'PL', 'City', true);
    }

    private function makeFlight(string $flightId = self::FLIGHT_ID): Flight
    {
        return Flight::fromPrimitives(
            $flightId,
            'LO123',
            self::DEPARTURE_ID,
            self::ARRIVAL_ID,
            new DateTimeImmutable('2024-12-25 10:00:00'),
            new DateTimeImmutable('2024-12-25 12:00:00'),
            'Boeing 737',
            180,
            'SCHEDULED',
        );
    }

    private function makeAvailability(string $flightId = self::FLIGHT_ID, int $totalSeats = 100): FlightAvailability
    {
        return FlightAvailability::initialize(
            AvailabilityId::generate(),
            $flightId,
            CabinClass::ECONOMY,
            $totalSeats,
        );
    }

    private function setupAirports(): void
    {
        $this->airportRepository->method('findByIataCode')
            ->willReturnCallback(fn (IataCode $code) => match ($code->getValue()) {
                'KTW' => $this->makeAirport(self::DEPARTURE_ID, 'KTW'),
                'WAW' => $this->makeAirport(self::ARRIVAL_ID, 'WAW'),
                default => null,
            });
    }

    public function testReturnsAvailableFlightsWhenNoLimitConfigured(): void
    {
        $this->setupAirports();
        $this->limitRepository->method('findDailyLimitByIataCode')->willReturn(null);
        $this->flightRepository->method('countByDepartureAirportAndDate')->willReturn(0);
        $this->flightRepository->method('findByRoute')->willReturn([$this->makeFlight()]);

        $availability = $this->makeAvailability(totalSeats: 100);
        $this->availabilityRepository->method('findByFlightAndCabin')->willReturn($availability);

        $results = ($this->handler)(new CheckRouteAvailabilityQuery(
            departureIata: 'KTW',
            arrivalIata: 'WAW',
            date: '2024-12-25',
            passengerCount: 2,
            cabinClass: 'ECONOMY',
        ));

        $this->assertCount(1, $results);
        $this->assertInstanceOf(CheckAvailabilityResponseDTO::class, $results[0]);
        $this->assertSame(self::FLIGHT_ID, $results[0]->flightId);
    }

    public function testReturnsEmptyListWhenKtwLimitReached(): void
    {
        $this->setupAirports();
        $this->limitRepository->method('findDailyLimitByIataCode')->with('KTW')->willReturn(2);
        $this->flightRepository->method('countByDepartureAirportAndDate')->willReturn(2);
        $this->flightRepository->expects($this->never())->method('findByRoute');

        $results = ($this->handler)(new CheckRouteAvailabilityQuery(
            departureIata: 'KTW',
            arrivalIata: 'WAW',
            date: '2024-12-25',
            passengerCount: 1,
            cabinClass: 'ECONOMY',
        ));

        $this->assertSame([], $results);
    }

    public function testReturnsEmptyListWhenInsufficientSeats(): void
    {
        $this->setupAirports();
        $this->limitRepository->method('findDailyLimitByIataCode')->willReturn(null);
        $this->flightRepository->method('countByDepartureAirportAndDate')->willReturn(0);
        $this->flightRepository->method('findByRoute')->willReturn([$this->makeFlight()]);

        $availability = $this->makeAvailability(totalSeats: 3);
        $availability->book(2); // only 1 seat left

        $this->availabilityRepository->method('findByFlightAndCabin')->willReturn($availability);

        $results = ($this->handler)(new CheckRouteAvailabilityQuery(
            departureIata: 'KTW',
            arrivalIata: 'WAW',
            date: '2024-12-25',
            passengerCount: 2, // 2 requested, only 1 available
            cabinClass: 'ECONOMY',
        ));

        $this->assertSame([], $results);
    }

    public function testReturnsEmptyListWhenFlightHasNoAvailabilityRecord(): void
    {
        $this->setupAirports();
        $this->limitRepository->method('findDailyLimitByIataCode')->willReturn(null);
        $this->flightRepository->method('countByDepartureAirportAndDate')->willReturn(0);
        $this->flightRepository->method('findByRoute')->willReturn([$this->makeFlight()]);

        $this->availabilityRepository->method('findByFlightAndCabin')->willReturn(null);

        $results = ($this->handler)(new CheckRouteAvailabilityQuery(
            departureIata: 'KTW',
            arrivalIata: 'WAW',
            date: '2024-12-25',
            passengerCount: 1,
            cabinClass: 'ECONOMY',
        ));

        $this->assertSame([], $results);
    }

    public function testThrowsWhenDepartureAirportNotFound(): void
    {
        $this->airportRepository->method('findByIataCode')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        ($this->handler)(new CheckRouteAvailabilityQuery(
            departureIata: 'ZZZ',
            arrivalIata: 'WAW',
            date: '2024-12-25',
            passengerCount: 1,
            cabinClass: 'ECONOMY',
        ));
    }

    public function testKtwWithOnlyOneFlightScheduledReturnsResults(): void
    {
        $this->setupAirports();
        $this->limitRepository->method('findDailyLimitByIataCode')->with('KTW')->willReturn(2);
        $this->flightRepository->method('countByDepartureAirportAndDate')->willReturn(1);
        $this->flightRepository->method('findByRoute')->willReturn([$this->makeFlight()]);

        $availability = $this->makeAvailability(totalSeats: 100);
        $this->availabilityRepository->method('findByFlightAndCabin')->willReturn($availability);

        $results = ($this->handler)(new CheckRouteAvailabilityQuery(
            departureIata: 'KTW',
            arrivalIata: 'WAW',
            date: '2024-12-25',
            passengerCount: 1,
            cabinClass: 'ECONOMY',
        ));

        $this->assertCount(1, $results);
    }
}
