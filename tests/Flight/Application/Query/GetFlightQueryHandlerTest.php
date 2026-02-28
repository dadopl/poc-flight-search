<?php

declare(strict_types=1);

namespace App\Tests\Flight\Application\Query;

use App\Flight\Application\DTO\FlightResponse;
use App\Flight\Application\Query\GetFlightQuery;
use App\Flight\Application\Query\GetFlightQueryHandler;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Exception\FlightNotFoundException;
use App\Flight\Domain\Repository\FlightRepository;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetFlightQueryHandlerTest extends TestCase
{
    private FlightRepository&MockObject $repository;
    private GetFlightQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(FlightRepository::class);
        $this->handler = new GetFlightQueryHandler($this->repository);
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

    public function testReturnsFlightResponse(): void
    {
        $flight = $this->makeFlight();
        $this->repository->method('findByFlightNumber')->willReturn($flight);

        $result = ($this->handler)(new GetFlightQuery('LO123'));

        $this->assertInstanceOf(FlightResponse::class, $result);
        $this->assertSame('LO123', $result->flightNumber);
        $this->assertSame('SCHEDULED', $result->status);
        $this->assertSame('Boeing 737', $result->aircraftModel);
        $this->assertSame(180, $result->aircraftTotalSeats);
    }

    public function testThrowsWhenFlightNotFound(): void
    {
        $this->repository->method('findByFlightNumber')->willReturn(null);

        $this->expectException(FlightNotFoundException::class);

        ($this->handler)(new GetFlightQuery('LO123'));
    }
}
