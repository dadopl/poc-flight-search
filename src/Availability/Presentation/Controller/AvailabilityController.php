<?php

declare(strict_types=1);

namespace App\Availability\Presentation\Controller;

use App\Availability\Application\Command\InitializeFlightAvailabilityCommand;
use App\Availability\Application\Query\CheckRouteAvailabilityQuery;
use App\Availability\Domain\Aggregate\FlightAvailability;
use App\Availability\Domain\Repository\FlightAvailabilityRepository;
use App\Availability\Domain\ValueObject\CabinClass;
use App\Availability\Presentation\DTO\AvailabilityResponseDTO;
use App\Availability\Presentation\DTO\CheckAvailabilityRequestDTO;
use App\Availability\Presentation\DTO\CheckAvailabilityResponseDTO;
use App\Availability\Presentation\DTO\InitializeAvailabilityRequestDTO;
use App\Shared\Domain\Bus\Command\CommandBus;
use App\Shared\Domain\Bus\Query\QueryBus;
use App\Shared\Infrastructure\Http\ApiResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AvailabilityController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
        private readonly FlightAvailabilityRepository $availabilityRepository,
        private readonly ApiResponseFactory $responseFactory,
    ) {
    }

    #[Route('/api/availability/check', methods: ['GET'])]
    public function check(Request $request): JsonResponse
    {
        $dto = CheckAvailabilityRequestDTO::fromRequest($request);

        $errors = $dto->validate();
        if ($errors !== []) {
            return $this->responseFactory->validationError($errors);
        }

        /** @var CheckAvailabilityResponseDTO[] $results */
        $results = $this->queryBus->ask(new CheckRouteAvailabilityQuery(
            departureIata: (string) $dto->from,
            arrivalIata: (string) $dto->to,
            date: (string) $dto->date,
            passengerCount: (int) $dto->passengers,
            cabinClass: (string) $dto->cabin,
        ));

        return $this->responseFactory->success(
            array_map(static fn (CheckAvailabilityResponseDTO $r) => $r->toArray(), $results),
        );
    }

    #[Route('/api/flights/{flightId}/availability/initialize', methods: ['POST'])]
    public function initialize(string $flightId, Request $request): JsonResponse
    {
        /** @var array<string, mixed> $body */
        $body = json_decode($request->getContent(), true) ?? [];
        $dto = InitializeAvailabilityRequestDTO::fromArray($body);

        $errors = $dto->validate();
        if ($errors !== []) {
            return $this->responseFactory->validationError($errors);
        }

        $this->commandBus->execute(new InitializeFlightAvailabilityCommand(
            flightId: $flightId,
            cabinClass: (string) $dto->cabinClass,
            totalSeats: (int) $dto->totalSeats,
            minimumAvailableThreshold: $dto->minimumAvailableThreshold,
        ));

        $availability = $this->availabilityRepository->findByFlightAndCabin(
            $flightId,
            CabinClass::from((string) $dto->cabinClass),
        );

        return $this->responseFactory->success(
            $availability !== null ? AvailabilityResponseDTO::fromAggregate($availability)->toArray() : null,
            JsonResponse::HTTP_CREATED,
        );
    }

    #[Route('/api/flights/{flightId}/availability', methods: ['GET'])]
    public function getAvailability(string $flightId): JsonResponse
    {
        $availabilities = $this->availabilityRepository->findByFlightId($flightId);

        if ($availabilities === []) {
            return $this->responseFactory->error(
                sprintf('No availability found for flight "%s".', $flightId),
                JsonResponse::HTTP_NOT_FOUND,
            );
        }

        return $this->responseFactory->success(
            array_map(
                static fn (FlightAvailability $a) => AvailabilityResponseDTO::fromAggregate($a)->toArray(),
                $availabilities,
            ),
        );
    }
}
