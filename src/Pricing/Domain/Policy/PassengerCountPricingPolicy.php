<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Policy;

use App\Pricing\Domain\Aggregate\PriceList;
use App\Pricing\Domain\Port\PricingPolicy;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class PassengerCountPricingPolicy implements PricingPolicy
{
    private const GROUP_THRESHOLD = 5;
    private const DISCOUNT_MODIFIER = 0.90;

    public function apply(
        Money $currentPrice,
        PriceList $priceList,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $departureTime,
        int $passengerCount,
        int $availableSeats,
        int $totalSeats,
    ): Money {
        if ($passengerCount >= self::GROUP_THRESHOLD) {
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
        if ($passengerCount >= self::GROUP_THRESHOLD) {
            return sprintf(
                'Group discount: -10%% (%d or more passengers)',
                self::GROUP_THRESHOLD,
            );
        }

        return null;
    }
}
