<?php

declare(strict_types=1);

namespace App\Tests\Flight\Application\Command;

use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportId;
use App\Airport\Domain\AirportName;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\City;
use App\Airport\Domain\Country;
use App\Airport\Domain\Exception\AirportNotFoundException;
use App\Airport\Domain\IataCode;
use App\Flight\Application\Command\ScheduleFlightCommand;
use App\Flight\Application\Command\ScheduleFlightCommandHandler;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Repository\FlightRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScheduleFlightCommandHandlerTest extends TestCase
{
    private FlightRepository&MockObject $flightRepository;
    private AirportRepository&MockObject $airportRepository;
    private ScheduleFlightCommandHandler $handler;

    protected function setUp(): void
    {
        $this->flightRepository = $this->createMock(FlightRepository::class);
        $this->airportRepository = $this->createMock(AirportRepository::class);
        $this->handler = new ScheduleFlightCommandHandler(
            $this->flightRepository,
            $this->airportRepository,
        );
    }

    private function makeAirport(string $iata, string $id): Airport
    {
        return Airport::fromPrimitives($id, $iata, 'Airport', 'PL', 'City', true);
    }

    public function testSchedulesAndSavesFlight(): void
    {
        $departure = $this->makeAirport('WAW', '550e8400-e29b-41d4-a716-446655440001');
        $arrival = $this->makeAirport('KTW', '550e8400-e29b-41d4-a716-446655440002');

        $this->airportRepository->method('findByIataCode')
            ->willReturnCallback(static fn (IataCode $code) => match ($code->getValue()) {
                'WAW' => $departure,
                'KTW' => $arrival,
                default => null,
            });

        $this->flightRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Flight::class));

        ($this->handler)(new ScheduleFlightCommand(
            flightNumber: 'LO123',
            departureAirportIata: 'WAW',
            arrivalAirportIata: 'KTW',
            departureTime: '2026-06-01 10:00:00',
            arrivalTime: '2026-06-01 12:00:00',
            aircraftModel: 'Boeing 737',
            aircraftTotalSeats: 180,
        ));
    }

    public function testThrowsWhenDepartureAirportNotFound(): void
    {
        $this->airportRepository->method('findByIataCode')->willReturn(null);

        $this->expectException(AirportNotFoundException::class);

        ($this->handler)(new ScheduleFlightCommand(
            flightNumber: 'LO123',
            departureAirportIata: 'WAW',
            arrivalAirportIata: 'KTW',
            departureTime: '2026-06-01 10:00:00',
            arrivalTime: '2026-06-01 12:00:00',
            aircraftModel: 'Boeing 737',
            aircraftTotalSeats: 180,
        ));
    }

    public function testThrowsWhenArrivalAirportNotFound(): void
    {
        $departure = $this->makeAirport('WAW', '550e8400-e29b-41d4-a716-446655440001');

        $this->airportRepository->method('findByIataCode')
            ->willReturnCallback(static fn (IataCode $code) => match ($code->getValue()) {
                'WAW' => $departure,
                default => null,
            });

        $this->expectException(AirportNotFoundException::class);

        ($this->handler)(new ScheduleFlightCommand(
            flightNumber: 'LO123',
            departureAirportIata: 'WAW',
            arrivalAirportIata: 'KTW',
            departureTime: '2026-06-01 10:00:00',
            arrivalTime: '2026-06-01 12:00:00',
            aircraftModel: 'Boeing 737',
            aircraftTotalSeats: 180,
        ));
    }
}
