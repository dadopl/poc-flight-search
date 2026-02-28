<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Port;

use App\Pricing\Domain\Aggregate\PriceList;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

interface PricingPolicy
{
    public function apply(
        Money $currentPrice,
        PriceList $priceList,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $departureTime,
        int $passengerCount,
        int $availableSeats,
        int $totalSeats,
    ): Money;

    public function describe(
        Money $currentPrice,
        PriceList $priceList,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $departureTime,
        int $passengerCount,
        int $availableSeats,
        int $totalSeats,
    ): ?string;
}
