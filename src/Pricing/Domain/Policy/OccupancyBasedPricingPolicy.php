<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Policy;

use App\Pricing\Domain\Aggregate\PriceList;
use App\Pricing\Domain\Port\PricingPolicy;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class OccupancyBasedPricingPolicy implements PricingPolicy
{
    private const LOW_AVAILABILITY_THRESHOLD = 0.20;
    private const SURCHARGE_MODIFIER = 1.20;

    public function apply(
        Money $currentPrice,
        PriceList $priceList,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $departureTime,
        int $passengerCount,
        int $availableSeats,
        int $totalSeats,
    ): Money {
        if ($totalSeats > 0 && $this->isLowAvailability($availableSeats, $totalSeats)) {
            return $currentPrice->multiply(self::SURCHARGE_MODIFIER);
        }

        return $currentPrice;
    }

    public function describe(
        Money $currentPrice,
        PriceList $priceList,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $departureTime,
        int $passengerCount,
        int $availableSeats,
        int $totalSeats,
    ): ?string {
        if ($totalSeats > 0 && $this->isLowAvailability($availableSeats, $totalSeats)) {
            return sprintf(
                'High occupancy surcharge: +20%% (less than %d%% seats available)',
                (int) (self::LOW_AVAILABILITY_THRESHOLD * 100),
            );
        }

        return null;
    }

    private function isLowAvailability(int $availableSeats, int $totalSeats): bool
    {
        return ($availableSeats / $totalSeats) < self::LOW_AVAILABILITY_THRESHOLD;
    }
}
