<?php

declare(strict_types=1);

namespace App\Search\Application\CommandHandler;

use App\Search\Application\Command\ExecuteSearchCommand;
use App\Search\Application\Command\InitiateSearchCommand;
use App\Search\Domain\Aggregate\SearchSession;
use App\Search\Domain\Repository\SearchSessionRepository;
use App\Search\Domain\ValueObject\SearchCriteria;
use App\Search\Domain\ValueObject\SearchFilters;
use App\Search\Domain\ValueObject\SearchSessionId;
use App\Shared\Domain\Bus\Command\CommandBus;
use App\Shared\Domain\Bus\Command\CommandHandler;
use App\Shared\Domain\ValueObject\Money;

final class InitiateSearchCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly SearchSessionRepository $sessionRepository,
        private readonly CommandBus $commandBus,
    ) {
    }

    public function __invoke(InitiateSearchCommand $command): void
    {
        $criteria = new SearchCriteria(
            departureIata: $command->departureIata,
            arrivalIata: $command->arrivalIata,
            departureDate: $command->departureDate,
            returnDate: $command->returnDate,
            passengerCount: $command->passengerCount,
            cabinClass: $command->cabinClass,
        );

        $maxPrice = null;
        if ($command->maxPriceAmount !== null && $command->maxPriceCurrency !== null) {
            $maxPrice = new Money($command->maxPriceAmount, $command->maxPriceCurrency);
        }

        $filters = new SearchFilters(
            maxPrice: $maxPrice,
            maxDurationMinutes: $command->maxDurationMinutes,
            directOnly: $command->directOnly,
        );

        $session = SearchSession::initiate(
            new SearchSessionId($command->sessionId),
            $criteria,
            $filters,
        );

        $this->sessionRepository->save($session);

        $this->commandBus->execute(new ExecuteSearchCommand($command->sessionId));
    }
}
