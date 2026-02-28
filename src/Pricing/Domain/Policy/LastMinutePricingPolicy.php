<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Policy;

use App\Pricing\Domain\Aggregate\PriceList;
use App\Pricing\Domain\Port\PricingPolicy;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class LastMinutePricingPolicy implements PricingPolicy
{
    private const DAYS_THRESHOLD = 7;
    private const SURCHARGE_MODIFIER = 1.30;

    public function apply(
        Money $currentPrice,
        PriceList $priceList,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $departureTime,
        int $passengerCount,
        int $availableSeats,
        int $totalSeats,
    ): Money {
        if ($this->isLastMinute($purchaseDate, $departureTime)) {
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
        if ($this->isLastMinute($purchaseDate, $departureTime)) {
            return sprintf('Last minute surcharge: +30%% (purchased less than %d days before departure)', self::DAYS_THRESHOLD);
        }

        return null;
    }

    private function isLastMinute(DateTimeImmutable $purchaseDate, DateTimeImmutable $departureTime): bool
    {
        $daysUntilDeparture = (int) $purchaseDate->diff($departureTime)->days;

        return $daysUntilDeparture < self::DAYS_THRESHOLD;
    }
}
