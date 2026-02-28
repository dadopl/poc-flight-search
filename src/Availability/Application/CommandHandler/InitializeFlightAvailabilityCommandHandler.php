<?php

declare(strict_types=1);

namespace App\Availability\Application\CommandHandler;

use App\Availability\Application\Command\InitializeFlightAvailabilityCommand;
use App\Availability\Domain\Aggregate\FlightAvailability;
use App\Availability\Domain\Repository\FlightAvailabilityRepository;
use App\Availability\Domain\ValueObject\AvailabilityId;
use App\Availability\Domain\ValueObject\CabinClass;
use App\Shared\Domain\Bus\Command\CommandHandler;

final class InitializeFlightAvailabilityCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly FlightAvailabilityRepository $repository,
    ) {
    }

    public function __invoke(InitializeFlightAvailabilityCommand $command): void
    {
        $availability = FlightAvailability::initialize(
            AvailabilityId::generate(),
            $command->flightId,
            CabinClass::from($command->cabinClass),
            $command->totalSeats,
            $command->minimumAvailableThreshold,
        );

        $this->repository->save($availability);
    }
}
