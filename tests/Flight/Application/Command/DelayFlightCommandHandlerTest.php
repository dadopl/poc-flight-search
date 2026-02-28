<?php

declare(strict_types=1);

namespace App\Tests\Flight\Application\Command;

use App\Flight\Application\Command\DelayFlightCommand;
use App\Flight\Application\Command\DelayFlightCommandHandler;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Exception\FlightNotFoundException;
use App\Flight\Domain\Repository\FlightRepository;
use App\Flight\Domain\ValueObject\FlightStatus;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DelayFlightCommandHandlerTest extends TestCase
{
    private FlightRepository&MockObject $repository;
    private DelayFlightCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(FlightRepository::class);
        $this->handler = new DelayFlightCommandHandler($this->repository);
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

    public function testDelaysFlight(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->repository->method('findByFlightNumber')->willReturn($flight);
        $this->repository->expects($this->once())->method('save')->with($flight);

        ($this->handler)(new DelayFlightCommand('LO123', '2026-06-01 11:00:00'));

        $this->assertSame(FlightStatus::DELAYED, $flight->getStatus());
    }

    public function testThrowsWhenFlightNotFound(): void
    {
        $this->repository->method('findByFlightNumber')->willReturn(null);

        $this->expectException(FlightNotFoundException::class);

        ($this->handler)(new DelayFlightCommand('LO123', '2026-06-01 11:00:00'));
    }
}
