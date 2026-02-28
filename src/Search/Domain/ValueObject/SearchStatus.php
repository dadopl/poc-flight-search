<?php

declare(strict_types=1);

namespace App\Search\Domain\ValueObject;

enum SearchStatus: string
{
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
}
