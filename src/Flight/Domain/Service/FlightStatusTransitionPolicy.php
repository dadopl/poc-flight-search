<?php

declare(strict_types=1);

namespace App\Flight\Domain\Service;

use App\Flight\Domain\Exception\InvalidFlightStatusTransitionException;
use App\Flight\Domain\ValueObject\FlightStatus;

final class FlightStatusTransitionPolicy
{
    /** @var array<string, FlightStatus[]> */
    private const ALLOWED_TRANSITIONS = [
        FlightStatus::SCHEDULED->value => [
            FlightStatus::BOARDING,
            FlightStatus::DELAYED,
            FlightStatus::CANCELLED,
        ],
        FlightStatus::BOARDING->value => [
            FlightStatus::DEPARTED,
            FlightStatus::CANCELLED,
        ],
        FlightStatus::DEPARTED->value => [
            FlightStatus::ARRIVED,
        ],
        FlightStatus::ARRIVED->value => [],
        FlightStatus::CANCELLED->value => [],
        FlightStatus::DELAYED->value => [
            FlightStatus::BOARDING,
            FlightStatus::CANCELLED,
        ],
    ];

    /** @return FlightStatus[] */
    public static function getAllowedTransitions(FlightStatus $from): array
    {
        return self::ALLOWED_TRANSITIONS[$from->value];
    }

    public static function assertCanTransition(FlightStatus $from, FlightStatus $to): void
    {
        $allowed = self::ALLOWED_TRANSITIONS[$from->value];

        foreach ($allowed as $allowedStatus) {
            if ($allowedStatus === $to) {
                return;
            }
        }

        throw InvalidFlightStatusTransitionException::fromTo($from, $to);
    }
}
