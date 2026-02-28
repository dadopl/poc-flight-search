<?php

declare(strict_types=1);

namespace App\Flight\Domain\ValueObject;

enum FlightStatus: string
{
    case SCHEDULED = 'SCHEDULED';
    case BOARDING = 'BOARDING';
    case DEPARTED = 'DEPARTED';
    case ARRIVED = 'ARRIVED';
    case CANCELLED = 'CANCELLED';
    case DELAYED = 'DELAYED';
}
