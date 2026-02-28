<?php

declare(strict_types=1);

namespace App\Pricing\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Money;

final class PriceCalculationResult
{
    /** @param string[] $appliedRules */
    public function __construct(
        private readonly Money $finalPrice,
        private readonly array $appliedRules,
    ) {
    }

    public function getFinalPrice(): Money
    {
        return $this->finalPrice;
    }

    /** @return string[] */
    public function getAppliedRules(): array
    {
        return $this->appliedRules;
    }
}
