<?php

declare(strict_types=1);

namespace App\Tests\Flight\Application\Query;

use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportId;
use App\Airport\Domain\AirportName;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\City;
use App\Airport\Domain\Country;
use App\Airport\Domain\IataCode;
use App\Flight\Application\DTO\FlightResponse;
use App\Flight\Application\Query\ListFlightsByRouteQuery;
use App\Flight\Application\Query\ListFlightsByRouteQueryHandler;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Repository\FlightRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListFlightsByRouteQueryHandlerTest extends TestCase
{
    private FlightRepository&MockObject $flightRepository;
    private AirportRepository&MockObject $airportRepository;
    private ListFlightsByRouteQueryHandler $handler;

    protected function setUp(): void
    {
        $this->flightRepository = $this->createMock(FlightRepository::class);
        $this->airportRepository = $this->createMock(AirportRepository::class);
        $this->handler = new ListFlightsByRouteQueryHandler(
            $this->flightRepository,
            $this->airportRepository,
        );
    }

    private function makeAirport(string $iata, string $id): Airport
    {
        return Airport::fromPrimitives($id, $iata, 'Airport', 'PL', 'City', true);
    }

    private function makeFlight(): Flight
    {
        return Flight::fromPrimitives(
            '550e8400-e29b-41d4-a716-446655440000',
            'LO123',
            '550e8400-e29b-41d4-a716-446655440001',
            '550e8400-e29b-41d4-a716-446655440002',
            new DateTimeImmutable('2026-06-01 10:00:00'),
            new DateTimeImmutable('2026-06-01 12:00:00'),
            'Boeing 737',
            180,
            'SCHEDULED',
        );
    }

    public function testReturnsFlightResponseList(): void
    {
        $departure = $this->makeAirport('WAW', '550e8400-e29b-41d4-a716-446655440001');
        $arrival = $this->makeAirport('KTW', '550e8400-e29b-41d4-a716-446655440002');

        $this->airportRepository->method('findByIataCode')
            ->willReturnCallback(static fn (IataCode $code) => match ($code->getValue()) {
                'WAW' => $departure,
                'KTW' => $arrival,
                default => null,
            });

        $this->flightRepository->method('findByRoute')->willReturn([$this->makeFlight()]);

        $result = ($this->handler)(new ListFlightsByRouteQuery('WAW', 'KTW', '2026-06-01'));

        $this->assertCount(1, $result);
        $this->assertInstanceOf(FlightResponse::class, $result[0]);
        $this->assertSame('LO123', $result[0]->flightNumber);
    }

    public function testThrowsWhenDepartureAirportNotFound(): void
    {
        $this->airportRepository->method('findByIataCode')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);

        ($this->handler)(new ListFlightsByRouteQuery('WAW', 'KTW', '2026-06-01'));
    }

    public function testThrowsWhenArrivalAirportNotFound(): void
    {
        $departure = $this->makeAirport('WAW', '550e8400-e29b-41d4-a716-446655440001');

        $this->airportRepository->method('findByIataCode')
            ->willReturnCallback(static fn (IataCode $code) => match ($code->getValue()) {
                'WAW' => $departure,
                default => null,
            });

        $this->expectException(InvalidArgumentException::class);

        ($this->handler)(new ListFlightsByRouteQuery('WAW', 'KTW', '2026-06-01'));
    }
}
