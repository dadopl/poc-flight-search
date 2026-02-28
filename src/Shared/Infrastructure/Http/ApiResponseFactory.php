<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiResponseFactory
{
    /** @param mixed $data */
    public function success(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'meta' => ['status' => 'ok'],
            'data' => $data,
        ], $status);
    }

    public function error(string $message, int $status): JsonResponse
    {
        return new JsonResponse([
            'meta'  => ['status' => 'error'],
            'error' => $message,
        ], $status);
    }

    /** @param string[] $messages */
    public function validationError(array $messages, int $status = 422): JsonResponse
    {
        return new JsonResponse([
            'meta'   => ['status' => 'error'],
            'errors' => $messages,
        ], $status);
    }
}
