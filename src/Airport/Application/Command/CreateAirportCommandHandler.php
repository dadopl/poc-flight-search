<?php

declare(strict_types=1);

namespace App\Airport\Application\Command;

use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportId;
use App\Airport\Domain\AirportName;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\City;
use App\Airport\Domain\Country;
use App\Airport\Domain\GeoCoordinates;
use App\Airport\Domain\IataCode;
use App\Shared\Domain\Bus\Command\CommandHandler;

final class CreateAirportCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly AirportRepository $repository,
    ) {
    }

    public function __invoke(CreateAirportCommand $command): void
    {
        $geoCoordinates = null;

        if ($command->latitude !== null && $command->longitude !== null) {
            $geoCoordinates = new GeoCoordinates($command->latitude, $command->longitude);
        }

        $airport = Airport::create(
            AirportId::generate(),
            new IataCode($command->iataCode),
            new AirportName($command->name),
            new Country($command->country),
            new City($command->city),
            $geoCoordinates,
        );

        $this->repository->save($airport);
    }
}
