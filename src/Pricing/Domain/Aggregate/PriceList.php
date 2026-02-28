<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Aggregate;

use App\Pricing\Domain\ValueObject\PricingRule;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use InvalidArgumentException;

final class PriceList
{
    /** @param PricingRule[] $pricingRules */
    private function __construct(
        private readonly string $id,
        private readonly string $flightId,
        private readonly string $cabinClass,
        private readonly Money $basePrice,
        private readonly array $pricingRules,
        private readonly DateTimeImmutable $validFrom,
        private readonly DateTimeImmutable $validTo,
        private readonly bool $isActive,
    ) {
    }

    /** @param PricingRule[] $pricingRules */
    public static function create(
        string $id,
        string $flightId,
        string $cabinClass,
        Money $basePrice,
        array $pricingRules,
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
    ): self {
        if ($validTo <= $validFrom) {
            throw new InvalidArgumentException('validTo must be after validFrom');
        }

        return new self(
            $id,
            $flightId,
            $cabinClass,
            $basePrice,
            $pricingRules,
            $validFrom,
            $validTo,
            true,
        );
    }

    /** @param PricingRule[] $pricingRules */
    public static function fromPrimitives(
        string $id,
        string $flightId,
        string $cabinClass,
        int $basePriceAmount,
        string $basePriceCurrency,
        array $pricingRules,
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
        bool $isActive,
    ): self {
        return new self(
            $id,
            $flightId,
            $cabinClass,
            new Money($basePriceAmount, $basePriceCurrency),
            $pricingRules,
            $validFrom,
            $validTo,
            $isActive,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFlightId(): string
    {
        return $this->flightId;
    }

    public function getCabinClass(): string
    {
        return $this->cabinClass;
    }

    public function getBasePrice(): Money
    {
        return $this->basePrice;
    }

    /** @return PricingRule[] */
    public function getPricingRules(): array
    {
        return $this->pricingRules;
    }

    public function getValidFrom(): DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function getValidTo(): DateTimeImmutable
    {
        return $this->validTo;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
