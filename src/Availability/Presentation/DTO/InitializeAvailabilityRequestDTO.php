<?php

declare(strict_types=1);

namespace App\Availability\Presentation\DTO;

use App\Availability\Domain\ValueObject\CabinClass;

final class InitializeAvailabilityRequestDTO
{
    public function __construct(
        public readonly ?int $totalSeats,
        public readonly ?string $cabinClass,
        public readonly int $minimumAvailableThreshold,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            totalSeats: isset($data['totalSeats']) ? (int) $data['totalSeats'] : null,
            cabinClass: isset($data['cabinClass']) ? (string) $data['cabinClass'] : null,
            minimumAvailableThreshold: isset($data['minimumAvailableThreshold'])
                ? (int) $data['minimumAvailableThreshold']
                : 0,
        );
    }

    /** @return string[] */
    public function validate(): array
    {
        $errors = [];

        if ($this->totalSeats === null) {
            $errors[] = 'Field "totalSeats" is required.';
        } elseif ($this->totalSeats <= 0) {
            $errors[] = 'Field "totalSeats" must be a positive integer.';
        }

        if (empty($this->cabinClass)) {
            $errors[] = 'Field "cabinClass" is required.';
        } elseif (!in_array($this->cabinClass, array_column(CabinClass::cases(), 'value'), true)) {
            $errors[] = sprintf(
                'Field "cabinClass" must be one of: %s.',
                implode(', ', array_column(CabinClass::cases(), 'value')),
            );
        }

        if ($this->minimumAvailableThreshold < 0) {
            $errors[] = 'Field "minimumAvailableThreshold" must be a non-negative integer.';
        }

        return $errors;
    }
}
