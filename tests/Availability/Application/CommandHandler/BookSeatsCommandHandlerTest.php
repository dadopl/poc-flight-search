<?php

declare(strict_types=1);

namespace App\Tests\Availability\Application\CommandHandler;

use App\Availability\Application\Command\BookSeatsCommand;
use App\Availability\Application\CommandHandler\BookSeatsCommandHandler;
use App\Availability\Domain\Aggregate\FlightAvailability;
use App\Availability\Domain\Exception\InsufficientSeatsException;
use App\Availability\Domain\Exception\InvalidAvailabilityException;
use App\Availability\Domain\Repository\FlightAvailabilityRepository;
use App\Availability\Domain\ValueObject\AvailabilityId;
use App\Availability\Domain\ValueObject\CabinClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BookSeatsCommandHandlerTest extends TestCase
{
    private FlightAvailabilityRepository&MockObject $repository;
    private BookSeatsCommandHandler $handler;

    private const FLIGHT_ID = '550e8400-e29b-41d4-a716-446655440000';

    protected function setUp(): void
    {
        $this->repository = $this->createMock(FlightAvailabilityRepository::class);
        $this->handler = new BookSeatsCommandHandler($this->repository);
    }

    private function makeAvailability(int $totalSeats = 100): FlightAvailability
    {
        return FlightAvailability::initialize(
            AvailabilityId::generate(),
            self::FLIGHT_ID,
            CabinClass::ECONOMY,
            $totalSeats,
        );
    }

    public function testBooksSeatsSuccessfully(): void
    {
        $availability = $this->makeAvailability(100);

        $this->repository->method('findByFlightAndCabin')->willReturn($availability);
        $this->repository->expects($this->once())->method('save')
            ->with($this->isInstanceOf(FlightAvailability::class));

        ($this->handler)(new BookSeatsCommand(
            flightId: self::FLIGHT_ID,
            cabinClass: 'ECONOMY',
            count: 3,
        ));

        $this->assertSame(3, $availability->getBookedSeats());
    }

    public function testThrowsWhenAvailabilityNotFound(): void
    {
        $this->repository->method('findByFlightAndCabin')->willReturn(null);
        $this->repository->expects($this->never())->method('save');

        $this->expectException(InvalidAvailabilityException::class);

        ($this->handler)(new BookSeatsCommand(
            flightId: self::FLIGHT_ID,
            cabinClass: 'ECONOMY',
            count: 1,
        ));
    }

    public function testThrowsWhenInsufficientSeats(): void
    {
        $availability = $this->makeAvailability(2);

        $this->repository->method('findByFlightAndCabin')->willReturn($availability);
        $this->repository->expects($this->never())->method('save');

        $this->expectException(InsufficientSeatsException::class);

        ($this->handler)(new BookSeatsCommand(
            flightId: self::FLIGHT_ID,
            cabinClass: 'ECONOMY',
            count: 5,
        ));
    }
}
