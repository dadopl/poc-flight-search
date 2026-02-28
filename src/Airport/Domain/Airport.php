<?php

declare(strict_types=1);

namespace App\Airport\Domain;

use App\Airport\Domain\Event\AirportActivated;
use App\Airport\Domain\Event\AirportCreated;
use App\Airport\Domain\Event\AirportDeactivated;
use App\Shared\Domain\AggregateRoot;

final class Airport extends AggregateRoot
{
    private bool $active = false;

    private function __construct(
        private readonly AirportId $id,
        private readonly IataCode $iataCode,
        private readonly AirportName $name,
        private readonly Country $country,
        private readonly City $city,
        private readonly ?GeoCoordinates $geoCoordinates,
    ) {
    }

    public static function create(
        AirportId $id,
        IataCode $iataCode,
        AirportName $name,
        Country $country,
        City $city,
        ?GeoCoordinates $geoCoordinates = null,
    ): self {
        $airport = new self($id, $iataCode, $name, $country, $city, $geoCoordinates);
        $airport->recordEvent(new AirportCreated(
            $id->getValue(),
            $iataCode->getValue(),
            $name->getValue(),
            $country->getValue(),
            $city->getValue(),
        ));

        return $airport;
    }

    public static function fromPrimitives(
        string $id,
        string $iataCode,
        string $name,
        string $country,
        string $city,
        bool $active,
        ?GeoCoordinates $geoCoordinates = null,
    ): self {
        $airport = new self(
            new AirportId($id),
            new IataCode($iataCode),
            new AirportName($name),
            new Country($country),
            new City($city),
            $geoCoordinates,
        );
        $airport->active = $active;

        return $airport;
    }

    public function activate(): void
    {
        if ($this->active) {
            return;
        }

        $this->active = true;
        $this->recordEvent(new AirportActivated($this->id->getValue()));
    }

    public function deactivate(): void
    {
        if (!$this->active) {
            return;
        }

        $this->active = false;
        $this->recordEvent(new AirportDeactivated($this->id->getValue()));
    }

    public function getId(): AirportId
    {
        return $this->id;
    }

    public function getIataCode(): IataCode
    {
        return $this->iataCode;
    }

    public function getName(): AirportName
    {
        return $this->name;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function getGeoCoordinates(): ?GeoCoordinates
    {
        return $this->geoCoordinates;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
