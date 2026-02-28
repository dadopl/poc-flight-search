<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Policy;

use App\Pricing\Domain\Aggregate\PriceList;
use App\Pricing\Domain\Port\PricingPolicy;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class EarlyBirdPricingPolicy implements PricingPolicy
{
    private const DAYS_THRESHOLD = 30;
    private const DISCOUNT_MODIFIER = 0.85;

    public function apply(
        Money $currentPrice,
        PriceList $priceList,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $departureTime,
        int $passengerCount,
        int $availableSeats,
        int $totalSeats,
    ): Money {
        if ($this->isEarlyBird($purchaseDate, $departureTime)) {
            return $currentPrice->multiply(self::DISCOUNT_MODIFIER);
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
        if ($this->isEarlyBird($purchaseDate, $departureTime)) {
            return sprintf('Early bird discount: -15%% (purchased more than %d days before departure)', self::DAYS_THRESHOLD);
        }

        return null;
    }

    private function isEarlyBird(DateTimeImmutable $purchaseDate, DateTimeImmutable $departureTime): bool
    {
        $daysUntilDeparture = (int) $purchaseDate->diff($departureTime)->days;

        return $daysUntilDeparture > self::DAYS_THRESHOLD;
    }
}
