<?php

declare(strict_types=1);

namespace App\Search\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Money;

final class SearchResultItem
{
    public function __construct(
        public readonly string $flightId,
        public readonly string $flightNumber,
        public readonly string $departureIata,
        public readonly string $arrivalIata,
        public readonly string $departureTime,
        public readonly string $arrivalTime,
        public readonly int $availableSeats,
        public readonly string $cabinClass,
        public readonly Money $price,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'flightId'       => $this->flightId,
            'flightNumber'   => $this->flightNumber,
            'departureIata'  => $this->departureIata,
            'arrivalIata'    => $this->arrivalIata,
            'departureTime'  => $this->departureTime,
            'arrivalTime'    => $this->arrivalTime,
            'availableSeats' => $this->availableSeats,
            'cabinClass'     => $this->cabinClass,
            'price'          => [
                'amount'   => $this->price->getAmount(),
                'currency' => $this->price->getCurrency(),
            ],
        ];
    }
}
