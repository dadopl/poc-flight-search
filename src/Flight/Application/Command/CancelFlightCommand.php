<?php

declare(strict_types=1);

namespace App\Flight\Application\Command;

use App\Shared\Domain\Bus\Command\Command;

final class CancelFlightCommand implements Command
{
    public function __construct(
        public readonly string $flightNumber,
        public readonly string $reason,
    ) {
    }
}
