<?php

declare(strict_types=1);

namespace App\Tests\Flight\Application\Command;

use App\Flight\Application\Command\CancelFlightCommand;
use App\Flight\Application\Command\CancelFlightCommandHandler;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Exception\FlightNotFoundException;
use App\Flight\Domain\Repository\FlightRepository;
use App\Flight\Domain\ValueObject\FlightStatus;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CancelFlightCommandHandlerTest extends TestCase
{
    private FlightRepository&MockObject $repository;
    private CancelFlightCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(FlightRepository::class);
        $this->handler = new CancelFlightCommandHandler($this->repository);
    }

    private function makeScheduledFlight(): Flight
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

    public function testCancelsFlight(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->repository->method('findByFlightNumber')->willReturn($flight);
        $this->repository->expects($this->once())->method('save')->with($flight);

        ($this->handler)(new CancelFlightCommand('LO123', 'Bad weather'));

        $this->assertSame(FlightStatus::CANCELLED, $flight->getStatus());
    }

    public function testThrowsWhenFlightNotFound(): void
    {
        $this->repository->method('findByFlightNumber')->willReturn(null);

        $this->expectException(FlightNotFoundException::class);

        ($this->handler)(new CancelFlightCommand('LO123', 'Bad weather'));
    }
}
