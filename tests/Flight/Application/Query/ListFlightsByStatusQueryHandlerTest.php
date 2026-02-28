<?php

declare(strict_types=1);

namespace App\Tests\Flight\Application\Query;

use App\Flight\Application\DTO\FlightResponse;
use App\Flight\Application\Query\ListFlightsByStatusQuery;
use App\Flight\Application\Query\ListFlightsByStatusQueryHandler;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Repository\FlightRepository;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListFlightsByStatusQueryHandlerTest extends TestCase
{
    private FlightRepository&MockObject $repository;
    private ListFlightsByStatusQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(FlightRepository::class);
        $this->handler = new ListFlightsByStatusQueryHandler($this->repository);
    }

    private function makeFlight(string $status = 'SCHEDULED'): Flight
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
            $status,
        );
    }

    public function testReturnsAllFlightsWhenNoStatusFilter(): void
    {
        $this->repository->method('findByStatus')->willReturn([$this->makeFlight()]);

        $result = ($this->handler)(new ListFlightsByStatusQuery(null));

        $this->assertCount(1, $result);
        $this->assertInstanceOf(FlightResponse::class, $result[0]);
    }

    public function testReturnsFlightsFilteredByStatus(): void
    {
        $this->repository->method('findByStatus')->willReturn([$this->makeFlight('BOARDING')]);

        $result = ($this->handler)(new ListFlightsByStatusQuery('BOARDING'));

        $this->assertCount(1, $result);
        $this->assertSame('BOARDING', $result[0]->status);
    }

    public function testReturnsEmptyArrayWhenNoFlightsFound(): void
    {
        $this->repository->method('findByStatus')->willReturn([]);

        $result = ($this->handler)(new ListFlightsByStatusQuery(null));

        $this->assertSame([], $result);
    }
}
