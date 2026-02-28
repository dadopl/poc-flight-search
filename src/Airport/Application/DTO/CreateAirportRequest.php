<?php

declare(strict_types=1);

namespace App\Airport\Application\DTO;

final class CreateAirportRequest
{
    public string $iataCode = '';
    public string $name = '';
    public string $country = '';
    public string $city = '';
    public ?float $latitude = null;
    public ?float $longitude = null;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->iataCode = (string) ($data['iataCode'] ?? '');
        $request->name = (string) ($data['name'] ?? '');
        $request->country = (string) ($data['country'] ?? '');
        $request->city = (string) ($data['city'] ?? '');
        $request->latitude = isset($data['latitude']) ? (float) $data['latitude'] : null;
        $request->longitude = isset($data['longitude']) ? (float) $data['longitude'] : null;

        return $request;
    }

    /** @return string[] */
    public function validate(): array
    {
        $errors = [];

        if (trim($this->iataCode) === '') {
            $errors[] = 'iataCode is required.';
        }

        if (trim($this->name) === '') {
            $errors[] = 'name is required.';
        }

        if (trim($this->country) === '') {
            $errors[] = 'country is required.';
        }

        if (trim($this->city) === '') {
            $errors[] = 'city is required.';
        }

        if (($this->latitude === null) !== ($this->longitude === null)) {
            $errors[] = 'latitude and longitude must both be provided or both omitted.';
        }

        return $errors;
    }
}
