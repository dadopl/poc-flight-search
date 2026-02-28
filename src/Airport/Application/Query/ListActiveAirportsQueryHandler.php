<?php

declare(strict_types=1);

namespace App\Airport\Application\Query;

use App\Airport\Application\DTO\AirportResponse;
use App\Airport\Domain\AirportRepository;
use App\Shared\Domain\Bus\Query\QueryHandler;

final class ListActiveAirportsQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly AirportRepository $repository,
    ) {
    }

    /** @return AirportResponse[] */
    public function __invoke(ListActiveAirportsQuery $query): array
    {
        $airports = $this->repository->findAllActive();

        return array_map(
            static fn ($airport) => new AirportResponse(
                id: $airport->getId()->getValue(),
                iataCode: $airport->getIataCode()->getValue(),
                name: $airport->getName()->getValue(),
                country: $airport->getCountry()->getValue(),
                city: $airport->getCity()->getValue(),
                isActive: $airport->isActive(),
                latitude: $airport->getGeoCoordinates()?->getLatitude(),
                longitude: $airport->getGeoCoordinates()?->getLongitude(),
            ),
            $airports,
        );
    }
}
