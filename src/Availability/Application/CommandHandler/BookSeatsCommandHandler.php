<?php

declare(strict_types=1);

namespace App\Availability\Application\CommandHandler;

use App\Availability\Application\Command\BookSeatsCommand;
use App\Availability\Domain\Exception\InvalidAvailabilityException;
use App\Availability\Domain\Repository\FlightAvailabilityRepository;
use App\Availability\Domain\ValueObject\CabinClass;
use App\Shared\Domain\Bus\Command\CommandHandler;

final class BookSeatsCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly FlightAvailabilityRepository $repository,
    ) {
    }

    public function __invoke(BookSeatsCommand $command): void
    {
        $availability = $this->repository->findByFlightAndCabin(
            $command->flightId,
            CabinClass::from($command->cabinClass),
        );

        if ($availability === null) {
            throw new InvalidAvailabilityException(sprintf(
                'Flight availability not found for flight "%s" and cabin "%s".',
                $command->flightId,
                $command->cabinClass,
            ));
        }

        $availability->book($command->count);
        $this->repository->save($availability);
    }
}
