<?php

declare(strict_types=1);

namespace App\Airport\Domain;

interface AirportRepository
{
    public function findById(AirportId $id): ?Airport;

    public function findByIataCode(IataCode $iataCode): ?Airport;

    /** @return Airport[] */
    public function findAllActive(): array;

    public function save(Airport $airport): void;

    public function delete(Airport $airport): void;
}
