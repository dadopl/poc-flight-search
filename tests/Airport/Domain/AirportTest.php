<?php

declare(strict_types=1);

namespace App\Tests\Airport\Domain;

use App\Airport\Domain\Airport;
use App\Airport\Domain\IataCode;
use PHPUnit\Framework\TestCase;

class AirportTest extends TestCase
{
    private function makeAirport(
        string $iata = 'KTW',
        string $name = 'Katowice Airport',
        string $city = 'Katowice',
        string $country = 'PL',
    ): Airport {
        return new Airport(new IataCode($iata), $name, $city, $country);
    }

    public function testCreatesAirportWithCorrectAttributes(): void
    {
        $airport = $this->makeAirport();

        $this->assertSame('KTW', $airport->getIataCode()->getValue());
        $this->assertSame('Katowice Airport', $airport->getName());
        $this->assertSame('Katowice', $airport->getCity());
        $this->assertSame('PL', $airport->getCountryCode());
    }

    public function testCountryCodeNormalizesToUppercase(): void
    {
        $airport = $this->makeAirport(country: 'pl');

        $this->assertSame('PL', $airport->getCountryCode());
    }

    public function testRenameChangesName(): void
    {
        $airport = $this->makeAirport();
        $airport->rename('Pyrzowice Airport');

        $this->assertSame('Pyrzowice Airport', $airport->getName());
    }

    public function testChangeCityChangesCity(): void
    {
        $airport = $this->makeAirport();
        $airport->changeCity('Pyrzowice');

        $this->assertSame('Pyrzowice', $airport->getCity());
    }

    public function testGetIataCodeReturnsValueObject(): void
    {
        $airport = $this->makeAirport('WAW');

        $this->assertInstanceOf(IataCode::class, $airport->getIataCode());
        $this->assertTrue($airport->getIataCode()->equals(new IataCode('WAW')));
    }
}
