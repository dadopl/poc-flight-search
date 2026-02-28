<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Controller;

use App\Airport\Application\Command\ActivateAirportCommand;
use App\Airport\Application\Command\CreateAirportCommand;
use App\Airport\Application\Command\DeactivateAirportCommand;
use App\Airport\Application\DTO\AirportResponse;
use App\Airport\Application\DTO\CreateAirportRequest;
use App\Airport\Application\Query\GetAirportQuery;
use App\Airport\Application\Query\ListActiveAirportsQuery;
use App\Shared\Domain\Bus\Command\CommandBus;
use App\Shared\Domain\Bus\Query\QueryBus;
use App\Shared\Infrastructure\Http\ApiResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/airports')]
final class AirportController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
        private readonly ApiResponseFactory $responseFactory,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $body */
        $body = json_decode($request->getContent(), true) ?? [];
        $dto = CreateAirportRequest::fromArray($body);

        $errors = $dto->validate();
        if ($errors !== []) {
            return $this->responseFactory->validationError($errors);
        }

        $this->commandBus->execute(new CreateAirportCommand(
            iataCode: $dto->iataCode,
            name: $dto->name,
            country: $dto->country,
            city: $dto->city,
            latitude: $dto->latitude,
            longitude: $dto->longitude,
        ));

        return $this->responseFactory->success(null, JsonResponse::HTTP_CREATED);
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var AirportResponse[] $airports */
        $airports = $this->queryBus->ask(new ListActiveAirportsQuery());

        return $this->responseFactory->success(
            array_map(static fn (AirportResponse $r) => $r->toArray(), $airports),
        );
    }

    #[Route('/{iataCode}', methods: ['GET'])]
    public function get(string $iataCode): JsonResponse
    {
        /** @var AirportResponse $airport */
        $airport = $this->queryBus->ask(new GetAirportQuery($iataCode));

        return $this->responseFactory->success($airport->toArray());
    }

    #[Route('/{iataCode}/activate', methods: ['POST'])]
    public function activate(string $iataCode): JsonResponse
    {
        $this->commandBus->execute(new ActivateAirportCommand($iataCode));

        return $this->responseFactory->success(null);
    }

    #[Route('/{iataCode}/deactivate', methods: ['POST'])]
    public function deactivate(string $iataCode): JsonResponse
    {
        $this->commandBus->execute(new DeactivateAirportCommand($iataCode));

        return $this->responseFactory->success(null);
    }
}
