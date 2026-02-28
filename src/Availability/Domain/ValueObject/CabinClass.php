<?php

declare(strict_types=1);

namespace App\Availability\Domain\ValueObject;

enum CabinClass: string
{
    case ECONOMY = 'ECONOMY';
    case BUSINESS = 'BUSINESS';
    case FIRST = 'FIRST';
}
