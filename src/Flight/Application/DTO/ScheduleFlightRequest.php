<?php

declare(strict_types=1);

namespace App\Flight\Application\DTO;

final class ScheduleFlightRequest
{
    public string $flightNumber = '';
    public string $departureAirportIata = '';
    public string $arrivalAirportIata = '';
    public string $departureTime = '';
    public string $arrivalTime = '';
    public string $aircraftModel = '';
    public int $aircraftTotalSeats = 0;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->flightNumber = (string) ($data['flightNumber'] ?? '');
        $request->departureAirportIata = (string) ($data['departureAirportIata'] ?? '');
        $request->arrivalAirportIata = (string) ($data['arrivalAirportIata'] ?? '');
        $request->departureTime = (string) ($data['departureTime'] ?? '');
        $request->arrivalTime = (string) ($data['arrivalTime'] ?? '');
        $request->aircraftModel = (string) ($data['aircraftModel'] ?? '');
        $request->aircraftTotalSeats = isset($data['aircraftTotalSeats']) ? (int) $data['aircraftTotalSeats'] : 0;

        return $request;
    }

    /** @return string[] */
    public function validate(): array
    {
        $errors = [];

        if (trim($this->flightNumber) === '') {
            $errors[] = 'flightNumber is required.';
        }

        if (trim($this->departureAirportIata) === '') {
            $errors[] = 'departureAirportIata is required.';
        }

        if (trim($this->arrivalAirportIata) === '') {
            $errors[] = 'arrivalAirportIata is required.';
        }

        if (trim($this->departureTime) === '') {
            $errors[] = 'departureTime is required.';
        }

        if (trim($this->arrivalTime) === '') {
            $errors[] = 'arrivalTime is required.';
        }

        if (trim($this->aircraftModel) === '') {
            $errors[] = 'aircraftModel is required.';
        }

        if ($this->aircraftTotalSeats <= 0) {
            $errors[] = 'aircraftTotalSeats must be greater than 0.';
        }

        return $errors;
    }
}
