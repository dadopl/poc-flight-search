<?php

declare(strict_types=1);

namespace App\Flight\Application\DTO;

final class UpdateFlightStatusRequest
{
    public string $status = '';
    public ?string $reason = null;
    public ?string $newDepartureTime = null;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->status = (string) ($data['status'] ?? '');
        $request->reason = isset($data['reason']) ? (string) $data['reason'] : null;
        $request->newDepartureTime = isset($data['newDepartureTime']) ? (string) $data['newDepartureTime'] : null;

        return $request;
    }

    /** @return string[] */
    public function validate(): array
    {
        $errors = [];

        if (trim($this->status) === '') {
            $errors[] = 'status is required.';
        }

        return $errors;
    }
}
