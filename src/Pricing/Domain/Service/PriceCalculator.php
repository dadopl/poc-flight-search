<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Service;

use App\Pricing\Domain\Aggregate\PriceList;
use App\Pricing\Domain\Port\PricingPolicy;
use App\Pricing\Domain\ValueObject\PriceCalculationResult;
use DateTimeImmutable;

final class PriceCalculator
{
    /** @param PricingPolicy[] $policies */
    public function __construct(
        private readonly array $policies,
    ) {
    }

    public function calculate(
        PriceList $priceList,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $departureTime,
        int $passengerCount,
        int $availableSeats,
        int $totalSeats,
    ): PriceCalculationResult {
        $currentPrice = $priceList->getBasePrice();
        $appliedRules = [];

        foreach ($this->policies as $policy) {
            $description = $policy->describe(
                $currentPrice,
                $priceList,
                $purchaseDate,
                $departureTime,
                $passengerCount,
                $availableSeats,
                $totalSeats,
            );

            $newPrice = $policy->apply(
                $currentPrice,
                $priceList,
                $purchaseDate,
                $departureTime,
                $passengerCount,
                $availableSeats,
                $totalSeats,
            );

            if ($description !== null) {
                $appliedRules[] = $description;
            }

            $currentPrice = $newPrice;
        }

        return new PriceCalculationResult($currentPrice, $appliedRules);
    }
}
