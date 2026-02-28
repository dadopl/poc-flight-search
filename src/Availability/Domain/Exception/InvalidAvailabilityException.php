<?php

declare(strict_types=1);

namespace App\Availability\Domain\Exception;

use DomainException;

final class InvalidAvailabilityException extends DomainException
{
    public static function negativeTotalSeats(int $totalSeats): self
    {
        return new self(sprintf(
            'Total seats must be greater than zero, got %d.',
            $totalSeats,
        ));
    }

    public static function negativeThreshold(int $threshold): self
    {
        return new self(sprintf(
            'Minimum available threshold cannot be negative, got %d.',
            $threshold,
        ));
    }

    public static function thresholdExceedsTotalSeats(int $threshold, int $totalSeats): self
    {
        return new self(sprintf(
            'Minimum available threshold (%d) cannot exceed total seats (%d).',
            $threshold,
            $totalSeats,
        ));
    }

    public static function negativeCount(string $operation, int $count): self
    {
        return new self(sprintf(
            'Count for operation "%s" must be greater than zero, got %d.',
            $operation,
            $count,
        ));
    }

    public static function cancellationExceedsBookings(int $count, int $bookedSeats): self
    {
        return new self(sprintf(
            'Cannot cancel %d booking(s): only %d seat(s) are booked.',
            $count,
            $bookedSeats,
        ));
    }

    public static function releaseExceedsBlocked(int $count, int $blockedSeats): self
    {
        return new self(sprintf(
            'Cannot release %d blocked seat(s): only %d seat(s) are blocked.',
            $count,
            $blockedSeats,
        ));
    }
}
