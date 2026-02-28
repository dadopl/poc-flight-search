<?php

declare(strict_types=1);

namespace App\Search\Domain\Exception;

use DomainException;

final class InvalidSearchSessionStateException extends DomainException
{
    public static function cannotComplete(string $currentStatus): self
    {
        return new self(sprintf(
            'Cannot complete a search session in status "%s". Session must be in PROCESSING status.',
            $currentStatus,
        ));
    }

    public static function cannotFail(string $currentStatus): self
    {
        return new self(sprintf(
            'Cannot fail a search session in status "%s". Session must be in PROCESSING status.',
            $currentStatus,
        ));
    }

    public static function cannotStart(string $currentStatus): self
    {
        return new self(sprintf(
            'Cannot start a search session in status "%s". Session must be in PENDING status.',
            $currentStatus,
        ));
    }
}
