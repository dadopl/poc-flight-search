<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

final class Money
{
    public function __construct(
        private readonly int $amount,
        private readonly string $currency,
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException(sprintf('Invalid currency code: "%s"', $currency));
        }
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}
