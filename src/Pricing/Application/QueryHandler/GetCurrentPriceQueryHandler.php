<?php

declare(strict_types=1);

namespace App\Pricing\Application\QueryHandler;

use App\Pricing\Application\Query\GetCurrentPriceQuery;
use App\Pricing\Domain\Repository\PriceListRepository;
use App\Pricing\Domain\Service\PriceCalculator;
use App\Shared\Domain\Bus\Query\QueryHandler;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class GetCurrentPriceQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly PriceListRepository $priceListRepository,
        private readonly PriceCalculator $priceCalculator,
    ) {
    }

    public function __invoke(GetCurrentPriceQuery $query): ?Money
    {
        $priceList = $this->priceListRepository->findByFlightAndCabin(
            $query->flightId,
            $query->cabinClass,
        );

        if ($priceList === null) {
            return null;
        }

        $result = $this->priceCalculator->calculate(
            $priceList,
            new DateTimeImmutable(),
            new DateTimeImmutable($query->departureTime),
            $query->passengerCount,
            $query->availableSeats,
            $query->totalSeats,
        );

        return $result->getFinalPrice();
    }
}
