<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Airport\Domain\Exception\AirportNotFoundException;
use App\Airport\Domain\Exception\InvalidIataCodeException;
use App\Availability\Domain\Exception\InsufficientSeatsException;
use App\Availability\Domain\Exception\InvalidAvailabilityException;
use App\Flight\Domain\Exception\FlightNotFoundException;
use App\Flight\Domain\Exception\InvalidFlightStatusTransitionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AirportNotFoundException) {
            $event->setResponse(new JsonResponse([
                'meta'  => ['status' => 'error'],
                'error' => $exception->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND));

            return;
        }

        if ($exception instanceof FlightNotFoundException) {
            $event->setResponse(new JsonResponse([
                'meta'  => ['status' => 'error'],
                'error' => $exception->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND));

            return;
        }

        if ($exception instanceof InvalidFlightStatusTransitionException) {
            $event->setResponse(new JsonResponse([
                'meta'  => ['status' => 'error'],
                'error' => $exception->getMessage(),
            ], JsonResponse::HTTP_CONFLICT));

            return;
        }

        if ($exception instanceof InvalidIataCodeException) {
            $event->setResponse(new JsonResponse([
                'meta'  => ['status' => 'error'],
                'error' => $exception->getMessage(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));

            return;
        }

        if ($exception instanceof \InvalidArgumentException) {
            $event->setResponse(new JsonResponse([
                'meta'  => ['status' => 'error'],
                'error' => $exception->getMessage(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));

            return;
        }

        if ($exception instanceof InvalidAvailabilityException) {
            $event->setResponse(new JsonResponse([
                'meta'  => ['status' => 'error'],
                'error' => $exception->getMessage(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));

            return;
        }

        if ($exception instanceof InsufficientSeatsException) {
            $event->setResponse(new JsonResponse([
                'meta'  => ['status' => 'error'],
                'error' => $exception->getMessage(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
        }
    }
}
