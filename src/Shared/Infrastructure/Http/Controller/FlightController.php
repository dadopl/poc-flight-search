<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Controller;

use App\Flight\Application\Command\BoardFlightCommand;
use App\Flight\Application\Command\CancelFlightCommand;
use App\Flight\Application\Command\DelayFlightCommand;
use App\Flight\Application\Command\ScheduleFlightCommand;
use App\Flight\Application\DTO\FlightResponse;
use App\Flight\Application\DTO\ScheduleFlightRequest;
use App\Flight\Application\DTO\UpdateFlightStatusRequest;
use App\Flight\Application\Query\GetFlightQuery;
use App\Flight\Application\Query\ListFlightsByRouteQuery;
use App\Flight\Application\Query\ListFlightsByStatusQuery;
use App\Shared\Domain\Bus\Command\CommandBus;
use App\Shared\Domain\Bus\Query\QueryBus;
use App\Shared\Infrastructure\Http\ApiResponseFactory;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/flights')]
final class FlightController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
        private readonly ApiResponseFactory $responseFactory,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function schedule(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $body */
        $body = json_decode($request->getContent(), true) ?? [];
        $dto = ScheduleFlightRequest::fromArray($body);

        $errors = $dto->validate();
        if ($errors !== []) {
            return $this->responseFactory->validationError($errors);
        }

        $this->commandBus->execute(new ScheduleFlightCommand(
            flightNumber: $dto->flightNumber,
            departureAirportIata: $dto->departureAirportIata,
            arrivalAirportIata: $dto->arrivalAirportIata,
            departureTime: $dto->departureTime,
            arrivalTime: $dto->arrivalTime,
            aircraftModel: $dto->aircraftModel,
            aircraftTotalSeats: $dto->aircraftTotalSeats,
        ));

        return $this->responseFactory->success(null, JsonResponse::HTTP_CREATED);
    }

    #[Route('/{flightNumber}', methods: ['GET'])]
    public function get(string $flightNumber): JsonResponse
    {
        /** @var FlightResponse $flight */
        $flight = $this->queryBus->ask(new GetFlightQuery($flightNumber));

        return $this->responseFactory->success($flight->toArray());
    }

    #[Route('', methods: ['GET'])]
    public function listFlights(Request $request): JsonResponse
    {
        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $date = $request->query->get('date');
        $status = $request->query->get('status');
        $page = max(1, (int) $request->query->get('page', '1'));
        $limit = max(1, min(100, (int) $request->query->get('limit', '20')));

        if ($from !== null && $to !== null && $date !== null) {
            /** @var FlightResponse[] $flights */
            $flights = $this->queryBus->ask(new ListFlightsByRouteQuery(
                departureIata: (string) $from,
                arrivalIata: (string) $to,
                date: (string) $date,
            ));
        } else {
            /** @var FlightResponse[] $flights */
            $flights = $this->queryBus->ask(new ListFlightsByStatusQuery(
                status: $status !== null ? (string) $status : null,
                page: $page,
                limit: $limit,
            ));
        }

        return $this->responseFactory->success(
            array_map(static fn (FlightResponse $r) => $r->toArray(), $flights),
        );
    }

    #[Route('/{flightNumber}/status', methods: ['PATCH'])]
    public function updateStatus(string $flightNumber, Request $request): JsonResponse
    {
        /** @var array<string, mixed> $body */
        $body = json_decode($request->getContent(), true) ?? [];
        $dto = UpdateFlightStatusRequest::fromArray($body);

        $errors = $dto->validate();
        if ($errors !== []) {
            return $this->responseFactory->validationError($errors);
        }

        $targetStatus = strtoupper($dto->status);

        match ($targetStatus) {
            'DELAYED'   => $this->commandBus->execute(new DelayFlightCommand(
                flightNumber: $flightNumber,
                newDepartureTime: $dto->newDepartureTime ?? throw new InvalidArgumentException(
                    'newDepartureTime is required when status is DELAYED.',
                ),
            )),
            'CANCELLED' => $this->commandBus->execute(new CancelFlightCommand(
                flightNumber: $flightNumber,
                reason: $dto->reason ?? throw new InvalidArgumentException(
                    'reason is required when status is CANCELLED.',
                ),
            )),
            'BOARDING'  => $this->commandBus->execute(new BoardFlightCommand(
                flightNumber: $flightNumber,
            )),
            default => throw new InvalidArgumentException(
                sprintf('Status "%s" cannot be set via this endpoint.', $dto->status),
            ),
        };

        return $this->responseFactory->success(null);
    }
}
