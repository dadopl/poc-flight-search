<?php

declare(strict_types=1);

namespace App\Airport\Domain\Event;

use App\Shared\Domain\Event\DomainEvent;

final class AirportCreated extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $iataCode,
        private readonly string $name,
        private readonly string $country,
        private readonly string $city,
    ) {
        parent::__construct($aggregateId);
    }

    public function getIataCode(): string
    {
        return $this->iataCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCity(): string
    {
        return $this->city;
    }
}
