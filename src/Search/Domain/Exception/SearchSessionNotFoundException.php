<?php

declare(strict_types=1);

namespace App\Search\Domain\Exception;

use DomainException;

final class SearchSessionNotFoundException extends DomainException
{
    public static function forId(string $sessionId): self
    {
        return new self(sprintf('Search session with ID "%s" not found.', $sessionId));
    }
}
