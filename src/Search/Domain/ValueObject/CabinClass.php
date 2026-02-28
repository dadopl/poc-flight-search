<?php

declare(strict_types=1);

namespace App\Search\Domain\ValueObject;

enum CabinClass: string
{
    case ECONOMY = 'ECONOMY';
    case BUSINESS = 'BUSINESS';
    case FIRST = 'FIRST';
}
