<?php

declare(strict_types=1);

namespace App\Availability\Domain\Aggregate;

use App\Availability\Domain\Exception\InsufficientSeatsException;
use App\Availability\Domain\Exception\InvalidAvailabilityException;
use App\Availability\Domain\ValueObject\AvailabilityId;
use App\Availability\Domain\ValueObject\CabinClass;
use App\Shared\Domain\AggregateRoot;

final class FlightAvailability extends AggregateRoot
{
    private function __construct(
        private readonly AvailabilityId $id,
        private readonly string $flightId,
        private readonly CabinClass $cabinClass,
        private readonly int $totalSeats,
        private int $bookedSeats,
        private int $blockedSeats,
        private readonly int $minimumAvailableThreshold,
    ) {
    }

    public static function initialize(
        AvailabilityId $id,
        string $flightId,
        CabinClass $cabinClass,
        int $totalSeats,
        int $minimumAvailableThreshold = 0,
    ): self {
        if ($totalSeats <= 0) {
            throw InvalidAvailabilityException::negativeTotalSeats($totalSeats);
        }

        if ($minimumAvailableThreshold < 0) {
            throw InvalidAvailabilityException::negativeThreshold($minimumAvailableThreshold);
        }

        if ($minimumAvailableThreshold > $totalSeats) {
            throw InvalidAvailabilityException::thresholdExceedsTotalSeats($minimumAvailableThreshold, $totalSeats);
        }

        return new self(
            $id,
            $flightId,
            $cabinClass,
            $totalSeats,
            bookedSeats: 0,
            blockedSeats: 0,
            minimumAvailableThreshold: $minimumAvailableThreshold,
        );
    }

    public static function fromPrimitives(
        string $id,
        string $flightId,
        string $cabinClass,
        int $totalSeats,
        int $bookedSeats,
        int $blockedSeats,
        int $minimumAvailableThreshold,
    ): self {
        return new self(
            new AvailabilityId($id),
            $flightId,
            CabinClass::from($cabinClass),
            $totalSeats,
            $bookedSeats,
            $blockedSeats,
            $minimumAvailableThreshold,
        );
    }

    public function book(int $count): void
    {
        if ($count <= 0) {
            throw InvalidAvailabilityException::negativeCount('book', $count);
        }

        if ($count > $this->availableSeats()) {
            throw InsufficientSeatsException::forBooking($count, $this->availableSeats());
        }

        $this->bookedSeats += $count;
    }

    public function cancelBooking(int $count): void
    {
        if ($count <= 0) {
            throw InvalidAvailabilityException::negativeCount('cancelBooking', $count);
        }

        if ($count > $this->bookedSeats) {
            throw InvalidAvailabilityException::cancellationExceedsBookings($count, $this->bookedSeats);
        }

        $this->bookedSeats -= $count;
    }

    public function blockSeats(int $count): void
    {
        if ($count <= 0) {
            throw InvalidAvailabilityException::negativeCount('blockSeats', $count);
        }

        if ($count > $this->availableSeats()) {
            throw InsufficientSeatsException::forBlocking($count, $this->availableSeats());
        }

        $this->blockedSeats += $count;
    }

    public function releaseBlockedSeats(int $count): void
    {
        if ($count <= 0) {
            throw InvalidAvailabilityException::negativeCount('releaseBlockedSeats', $count);
        }

        if ($count > $this->blockedSeats) {
            throw InvalidAvailabilityException::releaseExceedsBlocked($count, $this->blockedSeats);
        }

        $this->blockedSeats -= $count;
    }

    public function availableSeats(): int
    {
        return $this->totalSeats - $this->bookedSeats - $this->blockedSeats;
    }

    public function isAvailable(): bool
    {
        return $this->availableSeats() > $this->minimumAvailableThreshold;
    }

    public function isNearlyFull(): bool
    {
        $threshold = (int) ceil($this->totalSeats * 0.1);

        return $this->availableSeats() <= $threshold && $this->availableSeats() > 0;
    }

    public function getId(): AvailabilityId
    {
        return $this->id;
    }

    public function getFlightId(): string
    {
        return $this->flightId;
    }

    public function getCabinClass(): CabinClass
    {
        return $this->cabinClass;
    }

    public function getTotalSeats(): int
    {
        return $this->totalSeats;
    }

    public function getBookedSeats(): int
    {
        return $this->bookedSeats;
    }

    public function getBlockedSeats(): int
    {
        return $this->blockedSeats;
    }

    public function getMinimumAvailableThreshold(): int
    {
        return $this->minimumAvailableThreshold;
    }
}
