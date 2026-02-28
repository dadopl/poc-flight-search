<?php

declare(strict_types=1);

namespace App\Flight\Application\Command;

use App\Flight\Domain\Exception\FlightNotFoundException;
use App\Flight\Domain\Repository\FlightRepository;
use App\Flight\Domain\ValueObject\FlightNumber;
use App\Shared\Domain\Bus\Command\CommandHandler;

final class BoardFlightCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly FlightRepository $flightRepository,
    ) {
    }

    public function __invoke(BoardFlightCommand $command): void
    {
        $flightNumber = new FlightNumber($command->flightNumber);
        $flight = $this->flightRepository->findByFlightNumber($flightNumber);

        if ($flight === null) {
            throw FlightNotFoundException::withFlightNumber($command->flightNumber);
        }

        $flight->board();

        $this->flightRepository->save($flight);
    }
}
