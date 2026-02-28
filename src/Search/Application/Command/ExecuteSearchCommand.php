<?php

declare(strict_types=1);

namespace App\Search\Application\Command;

use App\Shared\Domain\Bus\Command\Command;

final class ExecuteSearchCommand implements Command
{
    public function __construct(
        public readonly string $sessionId,
    ) {
    }
}
