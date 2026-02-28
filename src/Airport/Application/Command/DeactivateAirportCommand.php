<?php

declare(strict_types=1);

namespace App\Airport\Application\Command;

use App\Shared\Domain\Bus\Command\Command;

final class DeactivateAirportCommand implements Command
{
    public function __construct(
        public readonly string $iataCode,
    ) {
    }
}
