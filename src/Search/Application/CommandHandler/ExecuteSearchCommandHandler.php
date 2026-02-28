<?php

declare(strict_types=1);

namespace App\Search\Application\CommandHandler;

use App\Availability\Application\Query\CheckRouteAvailabilityQuery;
use App\Pricing\Application\Query\GetCurrentPriceQuery;
use App\Search\Application\Command\ExecuteSearchCommand;
use App\Search\Application\Port\SearchResultsCache;
use App\Search\Domain\Exception\SearchSessionNotFoundException;
use App\Search\Domain\Repository\SearchSessionRepository;
use App\Search\Domain\ValueObject\SearchFilters;
use App\Search\Domain\ValueObject\SearchResultItem;
use App\Search\Domain\ValueObject\SearchSessionId;
use App\Shared\Domain\Bus\Command\CommandHandler;
use App\Shared\Domain\Bus\Query\QueryBus;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class ExecuteSearchCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly SearchSessionRepository $sessionRepository,
        private readonly SearchResultsCache $cache,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(ExecuteSearchCommand $command): void
    {
        $sessionId = new SearchSessionId($command->sessionId);
        $session = $this->sessionRepository->findById($sessionId);

        if ($session === null) {
            throw SearchSessionNotFoundException::forId($command->sessionId);
        }

        $session->start();
        $this->sessionRepository->save($session);

        try {
            $criteria = $session->getCriteria();
            $filters = $session->getFilters();

            /** @var \App\Availability\Presentation\DTO\CheckAvailabilityResponseDTO[] $availableFlights */
            $availableFlights = $this->queryBus->ask(new CheckRouteAvailabilityQuery(
                departureIata: $criteria->getDepartureIata(),
                arrivalIata: $criteria->getArrivalIata(),
                date: $criteria->getDepartureDate(),
                passengerCount: $criteria->getPassengerCount(),
                cabinClass: $criteria->getCabinClass(),
            ));

            $results = [];

            foreach ($availableFlights as $flight) {
                /** @var Money|null $price */
                $price = $this->queryBus->ask(new GetCurrentPriceQuery(
                    flightId: $flight->flightId,
                    cabinClass: $flight->cabinClass,
                    departureTime: $flight->departureTime,
                    passengerCount: $criteria->getPassengerCount(),
                    availableSeats: $flight->availableSeats,
                    totalSeats: $flight->totalSeats,
                ));

                if ($price === null) {
                    continue;
                }

                $departureTime = new DateTimeImmutable($flight->departureTime);
                $arrivalTime   = new DateTimeImmutable($flight->arrivalTime);

                if (!$this->passesFilters($price, $departureTime, $arrivalTime, $filters)) {
                    continue;
                }

                $results[] = new SearchResultItem(
                    flightId: $flight->flightId,
                    flightNumber: $flight->flightNumber,
                    departureIata: $criteria->getDepartureIata(),
                    arrivalIata: $criteria->getArrivalIata(),
                    departureTime: $flight->departureTime,
                    arrivalTime: $flight->arrivalTime,
                    availableSeats: $flight->availableSeats,
                    cabinClass: $flight->cabinClass,
                    price: $price,
                );
            }

            usort($results, static fn (SearchResultItem $a, SearchResultItem $b) => $a->price->getAmount() <=> $b->price->getAmount());

            $this->cache->store($command->sessionId, $results);

            $session->complete(count($results));
        } catch (\Throwable $e) {
            $session->fail('Search execution failed. Please try again.');
        }

        $this->sessionRepository->save($session);
    }

    private function passesFilters(
        Money $price,
        DateTimeImmutable $departureTime,
        DateTimeImmutable $arrivalTime,
        SearchFilters $filters,
    ): bool {
        $maxPrice = $filters->getMaxPrice();
        if ($maxPrice !== null && !$price->isLessThanOrEqualTo($maxPrice)) {
            return false;
        }

        $maxDuration = $filters->getMaxDurationMinutes();
        if ($maxDuration !== null) {
            $durationMinutes = (int) (($arrivalTime->getTimestamp() - $departureTime->getTimestamp()) / 60);
            if ($durationMinutes > $maxDuration) {
                return false;
            }
        }

        // For POC all flights are direct — directOnly filter always passes
        return true;
    }
}
