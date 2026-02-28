<?php

declare(strict_types=1);

namespace App\Pricing\Domain\ValueObject;

final class PricingRule
{
    public function __construct(
        private readonly string $name,
        private readonly string $description,
        private readonly float $modifier,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getModifier(): float
    {
        return $this->modifier;
    }
}
