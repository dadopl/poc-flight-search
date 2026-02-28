<?php

declare(strict_types=1);

namespace App\Availability\Application\Command;

use App\Shared\Domain\Bus\Command\Command;

final class ReleaseSeatsCommand implements Command
{
    public function __construct(
        public readonly string $flightId,
        public readonly string $cabinClass,
        public readonly int $count,
    ) {
    }
}
