<?php

declare(strict_types=1);

namespace App\Search\Application\Port;

use App\Search\Application\Command\InitiateSearchCommand;
use App\Search\Application\Query\GetSearchResultsQuery;
use App\Search\Domain\ValueObject\SearchSessionId;
use App\Shared\Domain\Bus\Command\CommandBus;
use App\Shared\Domain\Bus\Query\QueryBus;

final class SearchPort
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function initiateSearch(
        string $departureIata,
        string $arrivalIata,
        string $departureDate,
        ?string $returnDate,
        int $passengerCount,
        string $cabinClass,
        ?int $maxPriceAmount = null,
        ?string $maxPriceCurrency = null,
        ?int $maxDurationMinutes = null,
        bool $directOnly = false,
    ): string {
        $sessionId = SearchSessionId::generate()->getValue();

        $this->commandBus->execute(new InitiateSearchCommand(
            sessionId: $sessionId,
            departureIata: $departureIata,
            arrivalIata: $arrivalIata,
            departureDate: $departureDate,
            returnDate: $returnDate,
            passengerCount: $passengerCount,
            cabinClass: $cabinClass,
            maxPriceAmount: $maxPriceAmount,
            maxPriceCurrency: $maxPriceCurrency,
            maxDurationMinutes: $maxDurationMinutes,
            directOnly: $directOnly,
        ));

        return $sessionId;
    }

    /**
     * @return array{items: array<array<string, mixed>>, total: int, page: int, perPage: int}
     */
    public function getSearchResults(string $sessionId, int $page = 1, int $perPage = 20): array
    {
        /** @var array{items: array<array<string, mixed>>, total: int, page: int, perPage: int} $result */
        $result = $this->queryBus->ask(new GetSearchResultsQuery(
            sessionId: $sessionId,
            page: $page,
            perPage: $perPage,
        ));

        return $result;
    }
}
