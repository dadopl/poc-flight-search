<?php

declare(strict_types=1);

namespace App\Airport\Application\Query;

use App\Airport\Application\DTO\AirportResponse;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\Exception\AirportNotFoundException;
use App\Airport\Domain\IataCode;
use App\Shared\Domain\Bus\Query\QueryHandler;

final class GetAirportQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly AirportRepository $repository,
    ) {
    }

    public function __invoke(GetAirportQuery $query): AirportResponse
    {
        $iataCode = new IataCode($query->iataCode);
        $airport = $this->repository->findByIataCode($iataCode);

        if ($airport === null) {
            throw AirportNotFoundException::withIataCode($query->iataCode);
        }

        return new AirportResponse(
            id: $airport->getId()->getValue(),
            iataCode: $airport->getIataCode()->getValue(),
            name: $airport->getName()->getValue(),
            country: $airport->getCountry()->getValue(),
            city: $airport->getCity()->getValue(),
            isActive: $airport->isActive(),
            latitude: $airport->getGeoCoordinates()?->getLatitude(),
            longitude: $airport->getGeoCoordinates()?->getLongitude(),
        );
    }
}
