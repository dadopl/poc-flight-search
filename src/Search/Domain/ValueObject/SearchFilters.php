<?php

declare(strict_types=1);

namespace App\Search\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Money;
use InvalidArgumentException;

final class SearchFilters
{
    public function __construct(
        private readonly ?Money $maxPrice,
        private readonly ?int $maxDurationMinutes,
        private readonly bool $directOnly,
    ) {
        if ($this->maxDurationMinutes !== null && $this->maxDurationMinutes <= 0) {
            throw new InvalidArgumentException('Max duration in minutes must be positive.');
        }
    }

    public function getMaxPrice(): ?Money
    {
        return $this->maxPrice;
    }

    public function getMaxDurationMinutes(): ?int
    {
        return $this->maxDurationMinutes;
    }

    public function isDirectOnly(): bool
    {
        return $this->directOnly;
    }
}
