<?php

declare(strict_types=1);

namespace App\Tests\Availability\Domain;

use App\Availability\Domain\Aggregate\FlightAvailability;
use App\Availability\Domain\Exception\InsufficientSeatsException;
use App\Availability\Domain\Exception\InvalidAvailabilityException;
use App\Availability\Domain\ValueObject\AvailabilityId;
use App\Availability\Domain\ValueObject\CabinClass;
use PHPUnit\Framework\TestCase;

class FlightAvailabilityTest extends TestCase
{
    private const FLIGHT_ID = '550e8400-e29b-41d4-a716-446655440000';

    private function makeAvailability(
        int $totalSeats = 100,
        int $minimumAvailableThreshold = 0,
        CabinClass $cabinClass = CabinClass::ECONOMY,
    ): FlightAvailability {
        return FlightAvailability::initialize(
            AvailabilityId::generate(),
            self::FLIGHT_ID,
            $cabinClass,
            $totalSeats,
            $minimumAvailableThreshold,
        );
    }

    // --- initialize() ---

    public function testInitializeCreatesAvailabilityWithZeroBookedAndBlocked(): void
    {
        $availability = $this->makeAvailability(100);

        $this->assertSame(0, $availability->getBookedSeats());
        $this->assertSame(0, $availability->getBlockedSeats());
        $this->assertSame(100, $availability->getTotalSeats());
        $this->assertSame(100, $availability->availableSeats());
    }

    public function testInitializeThrowsForZeroTotalSeats(): void
    {
        $this->expectException(InvalidAvailabilityException::class);

        $this->makeAvailability(0);
    }

    public function testInitializeThrowsForNegativeTotalSeats(): void
    {
        $this->expectException(InvalidAvailabilityException::class);

        $this->makeAvailability(-1);
    }

    public function testInitializeThrowsForNegativeThreshold(): void
    {
        $this->expectException(InvalidAvailabilityException::class);

        FlightAvailability::initialize(
            AvailabilityId::generate(),
            self::FLIGHT_ID,
            CabinClass::ECONOMY,
            100,
            -1,
        );
    }

    public function testInitializeThrowsWhenThresholdExceedsTotalSeats(): void
    {
        $this->expectException(InvalidAvailabilityException::class);

        FlightAvailability::initialize(
            AvailabilityId::generate(),
            self::FLIGHT_ID,
            CabinClass::ECONOMY,
            10,
            11,
        );
    }

    // --- book() ---

    public function testBookReducesAvailableSeats(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->book(5);

        $this->assertSame(5, $availability->getBookedSeats());
        $this->assertSame(95, $availability->availableSeats());
    }

    public function testBookThrowsWhenInsufficientSeats(): void
    {
        $availability = $this->makeAvailability(5);
        $availability->book(2); // 3 left

        $this->expectException(InsufficientSeatsException::class);
        $availability->book(5); // 5 requested, 3 available
    }

    public function testBookThrowsWhenCountIsZero(): void
    {
        $availability = $this->makeAvailability(100);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->book(0);
    }

    public function testBookThrowsWhenCountIsNegative(): void
    {
        $availability = $this->makeAvailability(100);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->book(-1);
    }

    public function testBookAllAvailableSeatsSucceeds(): void
    {
        $availability = $this->makeAvailability(10);
        $availability->book(10);

        $this->assertSame(0, $availability->availableSeats());
    }

    // --- cancelBooking() ---

    public function testCancelBookingRestoresAvailableSeats(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->book(10);
        $availability->cancelBooking(3);

        $this->assertSame(7, $availability->getBookedSeats());
        $this->assertSame(93, $availability->availableSeats());
    }

    public function testCancelBookingThrowsWhenExceedingBookedSeats(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->book(5);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->cancelBooking(6);
    }

    public function testCancelBookingThrowsWhenCountIsZero(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->book(5);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->cancelBooking(0);
    }

    public function testCancelBookingThrowsWhenNoBookingsExist(): void
    {
        $availability = $this->makeAvailability(100);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->cancelBooking(1);
    }

    // --- blockSeats() ---

    public function testBlockSeatsReducesAvailableSeats(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->blockSeats(10);

        $this->assertSame(10, $availability->getBlockedSeats());
        $this->assertSame(90, $availability->availableSeats());
    }

    public function testBlockSeatsThrowsWhenInsufficientSeats(): void
    {
        $availability = $this->makeAvailability(10);
        $availability->book(8); // 2 left

        $this->expectException(InsufficientSeatsException::class);
        $availability->blockSeats(5); // 5 requested, 2 available
    }

    public function testBlockSeatsThrowsWhenCountIsZero(): void
    {
        $availability = $this->makeAvailability(100);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->blockSeats(0);
    }

    public function testBookAndBlockTogetherCannotExceedTotalSeats(): void
    {
        $availability = $this->makeAvailability(10);
        $availability->book(6);
        $availability->blockSeats(4); // 6 + 4 = 10, exactly total

        $this->assertSame(0, $availability->availableSeats());

        $this->expectException(InsufficientSeatsException::class);
        $availability->book(1); // no more seats
    }

    public function testBlockAndBookTogetherCannotExceedTotalSeats(): void
    {
        $availability = $this->makeAvailability(10);
        $availability->blockSeats(6);
        $availability->book(4); // 4 + 6 = 10, exactly total

        $this->assertSame(0, $availability->availableSeats());

        $this->expectException(InsufficientSeatsException::class);
        $availability->blockSeats(1); // no more seats
    }

    // --- releaseBlockedSeats() ---

    public function testReleaseBlockedSeatsRestoresAvailableSeats(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->blockSeats(10);
        $availability->releaseBlockedSeats(4);

        $this->assertSame(6, $availability->getBlockedSeats());
        $this->assertSame(94, $availability->availableSeats());
    }

    public function testReleaseBlockedSeatsThrowsWhenExceedingBlocked(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->blockSeats(5);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->releaseBlockedSeats(6);
    }

    public function testReleaseBlockedSeatsThrowsWhenCountIsZero(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->blockSeats(5);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->releaseBlockedSeats(0);
    }

    public function testReleaseBlockedSeatsThrowsWhenNoneBlocked(): void
    {
        $availability = $this->makeAvailability(100);

        $this->expectException(InvalidAvailabilityException::class);
        $availability->releaseBlockedSeats(1);
    }

    // --- isAvailable() ---

    public function testIsAvailableReturnsTrueWhenSeatsAboveThreshold(): void
    {
        $availability = $this->makeAvailability(100, 5);

        $this->assertTrue($availability->isAvailable());
    }

    public function testIsAvailableReturnsFalseWhenSeatsAtThreshold(): void
    {
        $availability = $this->makeAvailability(10, 5);
        $availability->book(5); // 5 available = threshold

        $this->assertFalse($availability->isAvailable());
    }

    public function testIsAvailableReturnsFalseWhenSeatsExhausted(): void
    {
        $availability = $this->makeAvailability(10);
        $availability->book(10);

        $this->assertFalse($availability->isAvailable());
    }

    public function testIsAvailableReturnsTrueWithNoThreshold(): void
    {
        $availability = $this->makeAvailability(10, 0);
        $availability->book(9); // 1 seat left, threshold=0

        $this->assertTrue($availability->isAvailable());
    }

    // --- isNearlyFull() ---

    public function testIsNearlyFullReturnsFalseWhenManySeatsAvailable(): void
    {
        $availability = $this->makeAvailability(100);

        $this->assertFalse($availability->isNearlyFull());
    }

    public function testIsNearlyFullReturnsTrueWhenFewSeatsLeft(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->book(91); // 9 left = 9% of 100 (threshold is ceil(100*0.1)=10)

        $this->assertTrue($availability->isNearlyFull());
    }

    public function testIsNearlyFullReturnsFalseWhenNoSeatsAvailable(): void
    {
        $availability = $this->makeAvailability(100);
        $availability->book(100);

        $this->assertFalse($availability->isNearlyFull());
    }

    // --- fromPrimitives() ---

    public function testFromPrimitivesRestoresState(): void
    {
        $availability = FlightAvailability::fromPrimitives(
            '550e8400-e29b-41d4-a716-446655440001',
            self::FLIGHT_ID,
            'BUSINESS',
            200,
            50,
            10,
            5,
        );

        $this->assertSame(CabinClass::BUSINESS, $availability->getCabinClass());
        $this->assertSame(200, $availability->getTotalSeats());
        $this->assertSame(50, $availability->getBookedSeats());
        $this->assertSame(10, $availability->getBlockedSeats());
        $this->assertSame(140, $availability->availableSeats());
    }

    // --- CabinClass variants ---

    public function testEconomyCabinClass(): void
    {
        $availability = $this->makeAvailability(cabinClass: CabinClass::ECONOMY);

        $this->assertSame(CabinClass::ECONOMY, $availability->getCabinClass());
    }

    public function testBusinessCabinClass(): void
    {
        $availability = $this->makeAvailability(cabinClass: CabinClass::BUSINESS);

        $this->assertSame(CabinClass::BUSINESS, $availability->getCabinClass());
    }

    public function testFirstCabinClass(): void
    {
        $availability = $this->makeAvailability(cabinClass: CabinClass::FIRST);

        $this->assertSame(CabinClass::FIRST, $availability->getCabinClass());
    }
}
