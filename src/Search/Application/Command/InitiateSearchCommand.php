<?php

declare(strict_types=1);

namespace App\Search\Application\Command;

use App\Shared\Domain\Bus\Command\Command;

final class InitiateSearchCommand implements Command
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $departureIata,
        public readonly string $arrivalIata,
        public readonly string $departureDate,
        public readonly ?string $returnDate,
        public readonly int $passengerCount,
        public readonly string $cabinClass,
        public readonly ?int $maxPriceAmount,
        public readonly ?string $maxPriceCurrency,
        public readonly ?int $maxDurationMinutes,
        public readonly bool $directOnly,
    ) {
    }
}
