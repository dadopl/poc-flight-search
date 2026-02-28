<?php

declare(strict_types=1);

namespace App\Airport\Domain;

interface AirportRepository
{
    public function findByIataCode(IataCode $iataCode): ?Airport;

    /** @return Airport[] */
    public function findAll(): array;

    public function save(Airport $airport): void;

    public function delete(Airport $airport): void;
}
