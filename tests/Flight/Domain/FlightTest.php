<?php

declare(strict_types=1);

namespace App\Tests\Flight\Domain;

use App\Airport\Domain\AirportId;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Event\FlightArrived;
use App\Flight\Domain\Event\FlightBoardingStarted;
use App\Flight\Domain\Event\FlightCancelled;
use App\Flight\Domain\Event\FlightDelayed;
use App\Flight\Domain\Event\FlightDeparted;
use App\Flight\Domain\Event\FlightScheduled;
use App\Flight\Domain\Exception\InvalidFlightStatusTransitionException;
use App\Flight\Domain\Exception\InvalidFlightTimesException;
use App\Flight\Domain\ValueObject\Aircraft;
use App\Flight\Domain\ValueObject\FlightId;
use App\Flight\Domain\ValueObject\FlightNumber;
use App\Flight\Domain\ValueObject\FlightStatus;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FlightTest extends TestCase
{
    private AirportId $departureId;
    private AirportId $arrivalId;
    private DateTimeImmutable $departure;
    private DateTimeImmutable $arrival;
    private Aircraft $aircraft;

    protected function setUp(): void
    {
        $this->departureId = new AirportId('550e8400-e29b-41d4-a716-446655440001');
        $this->arrivalId = new AirportId('550e8400-e29b-41d4-a716-446655440002');
        $this->departure = new DateTimeImmutable('2026-06-01 10:00:00');
        $this->arrival = new DateTimeImmutable('2026-06-01 12:00:00');
        $this->aircraft = new Aircraft('Boeing 737', 180);
    }

    private function makeScheduledFlight(): Flight
    {
        return Flight::schedule(
            FlightId::generate(),
            new FlightNumber('LO123'),
            $this->departureId,
            $this->arrivalId,
            $this->departure,
            $this->arrival,
            $this->aircraft,
        );
    }

    // --- schedule() ---

    public function testScheduleCreatesFlightWithScheduledStatus(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->assertSame(FlightStatus::SCHEDULED, $flight->getStatus());
    }

    public function testScheduleRegistersFlightScheduledEvent(): void
    {
        $flight = $this->makeScheduledFlight();
        $events = $flight->pullEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(FlightScheduled::class, $events[0]);
    }

    public function testScheduleStoresCorrectData(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->assertSame('LO123', $flight->getFlightNumber()->getValue());
        $this->assertTrue($flight->getDepartureAirportId()->equals($this->departureId));
        $this->assertTrue($flight->getArrivalAirportId()->equals($this->arrivalId));
        $this->assertEquals($this->departure, $flight->getDepartureTime());
        $this->assertEquals($this->arrival, $flight->getArrivalTime());
    }

    public function testScheduleThrowsWhenSameAirport(): void
    {
        $this->expectException(InvalidFlightTimesException::class);

        Flight::schedule(
            FlightId::generate(),
            new FlightNumber('LO123'),
            $this->departureId,
            $this->departureId,
            $this->departure,
            $this->arrival,
            $this->aircraft,
        );
    }

    public function testScheduleThrowsWhenArrivalNotAfterDeparture(): void
    {
        $this->expectException(InvalidFlightTimesException::class);

        Flight::schedule(
            FlightId::generate(),
            new FlightNumber('LO123'),
            $this->departureId,
            $this->arrivalId,
            $this->departure,
            $this->departure,
            $this->aircraft,
        );
    }

    public function testScheduleThrowsWhenArrivalBeforeDeparture(): void
    {
        $this->expectException(InvalidFlightTimesException::class);

        Flight::schedule(
            FlightId::generate(),
            new FlightNumber('LO123'),
            $this->departureId,
            $this->arrivalId,
            $this->departure,
            new DateTimeImmutable('2026-06-01 09:00:00'),
            $this->aircraft,
        );
    }

    // --- board() ---

    public function testBoardTransitionsToBoarding(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->pullEvents();

        $flight->board();

        $this->assertSame(FlightStatus::BOARDING, $flight->getStatus());
        $events = $flight->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(FlightBoardingStarted::class, $events[0]);
    }

    public function testBoardFromDelayedTransitionsToBoarding(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->delay(new DateTimeImmutable('2026-06-01 10:30:00'));
        $flight->pullEvents();

        $flight->board();

        $this->assertSame(FlightStatus::BOARDING, $flight->getStatus());
    }

    public function testBoardFromBoardingThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->board();
    }

    public function testBoardFromDepartedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->board();
    }

    public function testBoardFromArrivedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();
        $flight->arrive();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->board();
    }

    public function testBoardFromCancelledThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->cancel('Technical issue');

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->board();
    }

    // --- depart() ---

    public function testDepartTransitionsToDeparted(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->pullEvents();

        $flight->depart();

        $this->assertSame(FlightStatus::DEPARTED, $flight->getStatus());
        $events = $flight->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(FlightDeparted::class, $events[0]);
    }

    public function testDepartFromScheduledThrows(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->depart();
    }

    public function testDepartFromArrivedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();
        $flight->arrive();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->depart();
    }

    public function testDepartFromCancelledThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->cancel('Technical issue');

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->depart();
    }

    // --- arrive() ---

    public function testArriveTransitionsToArrived(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();
        $flight->pullEvents();

        $flight->arrive();

        $this->assertSame(FlightStatus::ARRIVED, $flight->getStatus());
        $events = $flight->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(FlightArrived::class, $events[0]);
    }

    public function testArriveFromScheduledThrows(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->arrive();
    }

    public function testArriveFromBoardingThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->arrive();
    }

    public function testArriveFromArrivedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();
        $flight->arrive();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->arrive();
    }

    public function testArriveFromCancelledThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->cancel('Technical issue');

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->arrive();
    }

    // --- cancel() ---

    public function testCancelTransitionsToCancelled(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->pullEvents();

        $flight->cancel('Bad weather');

        $this->assertSame(FlightStatus::CANCELLED, $flight->getStatus());
        $events = $flight->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(FlightCancelled::class, $events[0]);
        $this->assertSame('Bad weather', $events[0]->getReason());
    }

    public function testCancelFromBoardingTransitionsToCancelled(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();

        $flight->cancel('Safety issue');

        $this->assertSame(FlightStatus::CANCELLED, $flight->getStatus());
    }

    public function testCancelFromDelayedTransitionsToCancelled(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->delay(new DateTimeImmutable('2026-06-01 10:30:00'));

        $flight->cancel('Strike');

        $this->assertSame(FlightStatus::CANCELLED, $flight->getStatus());
    }

    public function testCancelFromArrivedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();
        $flight->arrive();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->cancel('Test');
    }

    public function testCancelFromDepartedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->cancel('Test');
    }

    public function testCancelFromCancelledThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->cancel('First reason');

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->cancel('Second reason');
    }

    public function testCancelWithEmptyReasonThrows(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->expectException(InvalidArgumentException::class);
        $flight->cancel('');
    }

    public function testCancelWithWhitespaceOnlyReasonThrows(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->expectException(InvalidArgumentException::class);
        $flight->cancel('   ');
    }

    // --- delay() ---

    public function testDelayTransitionsToDelayed(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->pullEvents();

        $newDeparture = new DateTimeImmutable('2026-06-01 11:00:00');
        $flight->delay($newDeparture);

        $this->assertSame(FlightStatus::DELAYED, $flight->getStatus());
        $this->assertEquals($newDeparture, $flight->getDepartureTime());

        $events = $flight->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(FlightDelayed::class, $events[0]);
        $this->assertEquals($newDeparture, $events[0]->getNewDepartureTime());
    }

    public function testDelayFromBoardingThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->delay(new DateTimeImmutable('2026-06-01 11:00:00'));
    }

    public function testDelayFromDepartedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->delay(new DateTimeImmutable('2026-06-01 11:00:00'));
    }

    public function testDelayFromArrivedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->board();
        $flight->depart();
        $flight->arrive();

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->delay(new DateTimeImmutable('2026-06-01 11:00:00'));
    }

    public function testDelayFromCancelledThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->cancel('Test');

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->delay(new DateTimeImmutable('2026-06-01 11:00:00'));
    }

    public function testDelayWithNewDepartureAfterArrivalThrows(): void
    {
        $flight = $this->makeScheduledFlight();

        $this->expectException(InvalidArgumentException::class);
        $flight->delay(new DateTimeImmutable('2026-06-01 13:00:00'));
    }

    public function testDelayFromDelayedThrows(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->delay(new DateTimeImmutable('2026-06-01 10:30:00'));

        $this->expectException(InvalidFlightStatusTransitionException::class);
        $flight->delay(new DateTimeImmutable('2026-06-01 11:00:00'));
    }

    // --- fromPrimitives() ---

    public function testFromPrimitivesRestoresState(): void
    {
        $flight = Flight::fromPrimitives(
            '550e8400-e29b-41d4-a716-446655440000',
            'LO123',
            '550e8400-e29b-41d4-a716-446655440001',
            '550e8400-e29b-41d4-a716-446655440002',
            new DateTimeImmutable('2026-06-01 10:00:00'),
            new DateTimeImmutable('2026-06-01 12:00:00'),
            'Boeing 737',
            180,
            'BOARDING',
        );

        $this->assertSame(FlightStatus::BOARDING, $flight->getStatus());
        $this->assertSame('LO123', $flight->getFlightNumber()->getValue());
        $this->assertEmpty($flight->pullEvents());
    }

    // --- pullEvents() ---

    public function testPullEventsClearsEventList(): void
    {
        $flight = $this->makeScheduledFlight();
        $flight->pullEvents();

        $this->assertEmpty($flight->pullEvents());
    }
}
