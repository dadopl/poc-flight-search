<?php

declare(strict_types=1);

namespace App\Airport\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'airports')]
class Airport
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 3)]
    private string $iataCode;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $city;

    #[ORM\Column(type: 'string', length: 2)]
    private string $countryCode;

    public function __construct(
        IataCode $iataCode,
        string $name,
        string $city,
        string $countryCode,
    ) {
        $this->iataCode = $iataCode->getValue();
        $this->name = $name;
        $this->city = $city;
        $this->countryCode = strtoupper(trim($countryCode));
    }

    public function getIataCode(): IataCode
    {
        return new IataCode($this->iataCode);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changeCity(string $city): void
    {
        $this->city = $city;
    }
}
