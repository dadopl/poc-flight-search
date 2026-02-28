<?php

declare(strict_types=1);

namespace App\Flight\Domain\ValueObject;

use InvalidArgumentException;

final class Aircraft
{
    public function __construct(
        private readonly string $model,
        private readonly int $totalSeats,
    ) {
        if (trim($model) === '') {
            throw new InvalidArgumentException('Aircraft model cannot be empty.');
        }

        if ($totalSeats <= 0) {
            throw new InvalidArgumentException(
                sprintf('Aircraft total seats must be greater than 0, got %d.', $totalSeats),
            );
        }
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getTotalSeats(): int
    {
        return $this->totalSeats;
    }

    public function equals(self $other): bool
    {
        return $this->model === $other->model && $this->totalSeats === $other->totalSeats;
    }
}
