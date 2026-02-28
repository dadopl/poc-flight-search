<?php

declare(strict_types=1);

namespace App\Airport\Application\DTO;

final class AirportResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $iataCode,
        public readonly string $name,
        public readonly string $country,
        public readonly string $city,
        public readonly bool $isActive,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'iataCode'   => $this->iataCode,
            'name'       => $this->name,
            'country'    => $this->country,
            'city'       => $this->city,
            'isActive'   => $this->isActive,
            'latitude'   => $this->latitude,
            'longitude'  => $this->longitude,
        ];
    }
}
