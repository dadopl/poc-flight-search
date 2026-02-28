<?php

declare(strict_types=1);

namespace App\Tests\Availability\Application\CommandHandler;

use App\Availability\Application\Command\InitializeFlightAvailabilityCommand;
use App\Availability\Application\CommandHandler\InitializeFlightAvailabilityCommandHandler;
use App\Availability\Domain\Aggregate\FlightAvailability;
use App\Availability\Domain\Exception\InvalidAvailabilityException;
use App\Availability\Domain\Repository\FlightAvailabilityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InitializeFlightAvailabilityCommandHandlerTest extends TestCase
{
    private FlightAvailabilityRepository&MockObject $repository;
    private InitializeFlightAvailabilityCommandHandler $handler;

    private const FLIGHT_ID = '550e8400-e29b-41d4-a716-446655440000';

    protected function setUp(): void
    {
        $this->repository = $this->createMock(FlightAvailabilityRepository::class);
        $this->handler = new InitializeFlightAvailabilityCommandHandler($this->repository);
    }

    public function testInitializesAndSavesAvailability(): void
    {
        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(FlightAvailability::class));

        ($this->handler)(new InitializeFlightAvailabilityCommand(
            flightId: self::FLIGHT_ID,
            cabinClass: 'ECONOMY',
            totalSeats: 180,
            minimumAvailableThreshold: 10,
        ));
    }

    public function testInitializesWithDefaultThreshold(): void
    {
        $savedAvailability = null;

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(static function (FlightAvailability $availability) use (&$savedAvailability): void {
                $savedAvailability = $availability;
            });

        ($this->handler)(new InitializeFlightAvailabilityCommand(
            flightId: self::FLIGHT_ID,
            cabinClass: 'BUSINESS',
            totalSeats: 50,
        ));

        $this->assertNotNull($savedAvailability);
        $this->assertSame(50, $savedAvailability->getTotalSeats());
        $this->assertSame(0, $savedAvailability->getMinimumAvailableThreshold());
        $this->assertSame(0, $savedAvailability->getBookedSeats());
        $this->assertSame(0, $savedAvailability->getBlockedSeats());
    }

    public function testThrowsWhenTotalSeatsIsZero(): void
    {
        $this->repository->expects($this->never())->method('save');

        $this->expectException(InvalidAvailabilityException::class);

        ($this->handler)(new InitializeFlightAvailabilityCommand(
            flightId: self::FLIGHT_ID,
            cabinClass: 'ECONOMY',
            totalSeats: 0,
        ));
    }

    public function testThrowsWhenTotalSeatsIsNegative(): void
    {
        $this->repository->expects($this->never())->method('save');

        $this->expectException(InvalidAvailabilityException::class);

        ($this->handler)(new InitializeFlightAvailabilityCommand(
            flightId: self::FLIGHT_ID,
            cabinClass: 'ECONOMY',
            totalSeats: -10,
        ));
    }

    public function testThrowsWhenThresholdExceedsTotalSeats(): void
    {
        $this->repository->expects($this->never())->method('save');

        $this->expectException(InvalidAvailabilityException::class);

        ($this->handler)(new InitializeFlightAvailabilityCommand(
            flightId: self::FLIGHT_ID,
            cabinClass: 'ECONOMY',
            totalSeats: 10,
            minimumAvailableThreshold: 11,
        ));
    }
}
