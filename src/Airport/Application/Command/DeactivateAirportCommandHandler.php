<?php

declare(strict_types=1);

namespace App\Airport\Application\Command;

use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\Exception\AirportNotFoundException;
use App\Airport\Domain\IataCode;
use App\Shared\Domain\Bus\Command\CommandHandler;

final class DeactivateAirportCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly AirportRepository $repository,
    ) {
    }

    public function __invoke(DeactivateAirportCommand $command): void
    {
        $iataCode = new IataCode($command->iataCode);
        $airport = $this->repository->findByIataCode($iataCode);

        if ($airport === null) {
            throw AirportNotFoundException::withIataCode($command->iataCode);
        }

        $airport->deactivate();
        $this->repository->save($airport);
    }
}
