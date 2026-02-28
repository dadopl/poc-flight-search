<?php

declare(strict_types=1);

namespace App\Airport\Domain;

use InvalidArgumentException;

final class GeoCoordinates
{
    public function __construct(
        private readonly float $latitude,
        private readonly float $longitude,
    ) {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new InvalidArgumentException(sprintf(
                'Invalid latitude "%s". Must be between -90 and 90.',
                $latitude,
            ));
        }

        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new InvalidArgumentException(sprintf(
                'Invalid longitude "%s". Must be between -180 and 180.',
                $longitude,
            ));
        }
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }
}
