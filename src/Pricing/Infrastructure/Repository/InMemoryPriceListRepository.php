<?php

declare(strict_types=1);

namespace App\Pricing\Infrastructure\Repository;

use App\Pricing\Domain\Aggregate\PriceList;
use App\Pricing\Domain\Repository\PriceListRepository;

final class InMemoryPriceListRepository implements PriceListRepository
{
    /** @var array<string, PriceList> */
    private array $priceLists = [];

    public function findByFlightAndCabin(string $flightId, string $cabinClass): ?PriceList
    {
        return $this->priceLists[$flightId . '_' . $cabinClass] ?? null;
    }

    public function save(PriceList $priceList): void
    {
        $this->priceLists[$priceList->getFlightId() . '_' . $priceList->getCabinClass()] = $priceList;
    }
}
