<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Repository;

use App\Pricing\Domain\Aggregate\PriceList;

interface PriceListRepository
{
    public function findByFlightAndCabin(string $flightId, string $cabinClass): ?PriceList;

    public function save(PriceList $priceList): void;
}
