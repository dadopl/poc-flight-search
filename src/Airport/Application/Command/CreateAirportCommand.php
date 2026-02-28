<?php

declare(strict_types=1);

namespace App\Airport\Application\Command;

use App\Shared\Domain\Bus\Command\Command;

final class CreateAirportCommand implements Command
{
    public function __construct(
        public readonly string $iataCode,
        public readonly string $name,
        public readonly string $country,
        public readonly string $city,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
    ) {
    }
}
