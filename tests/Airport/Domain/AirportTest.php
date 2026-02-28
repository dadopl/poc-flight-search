<?php

declare(strict_types=1);

namespace App\Tests\Airport\Domain;

use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportId;
use App\Airport\Domain\AirportName;
use App\Airport\Domain\City;
use App\Airport\Domain\Country;
use App\Airport\Domain\Event\AirportActivated;
use App\Airport\Domain\Event\AirportCreated;
use App\Airport\Domain\Event\AirportDeactivated;
use App\Airport\Domain\GeoCoordinates;
use App\Airport\Domain\IataCode;
use PHPUnit\Framework\TestCase;

class AirportTest extends TestCase
{
    private function makeAirport(
        string $iata = 'KTW',
        string $name = 'Katowice Airport',
        string $city = 'Katowice',
        string $country = 'PL',
        ?GeoCoordinates $geoCoordinates = null,
    ): Airport {
        return Airport::create(
            AirportId::generate(),
            new IataCode($iata),
            new AirportName($name),
            new Country($country),
            new City($city),
            $geoCoordinates,
        );
    }

    public function testCreatesAirportWithCorrectAttributes(): void
    {
        $airport = $this->makeAirport();

        $this->assertSame('KTW', $airport->getIataCode()->getValue());
        $this->assertSame('Katowice Airport', $airport->getName()->getValue());
        $this->assertSame('Katowice', $airport->getCity()->getValue());
        $this->assertSame('PL', $airport->getCountry()->getValue());
    }

    public function testNewAirportIsInactive(): void
    {
        $airport = $this->makeAirport();

        $this->assertFalse($airport->isActive());
    }

    public function testCreateRegistersAirportCreatedEvent(): void
    {
        $airport = $this->makeAirport();
        $events = $airport->pullEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(AirportCreated::class, $events[0]);
    }

    public function testPullEventsClearsEventList(): void
    {
        $airport = $this->makeAirport();
        $airport->pullEvents();
        $events = $airport->pullEvents();

        $this->assertEmpty($events);
    }

    public function testActivateRegistersActivatedEvent(): void
    {
        $airport = $this->makeAirport();
        $airport->pullEvents(); // clear creation event

        $airport->activate();
        $events = $airport->pullEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(AirportActivated::class, $events[0]);
        $this->assertTrue($airport->isActive());
    }

    public function testActivateOnAlreadyActiveAirportDoesNotRegisterDuplicateEvent(): void
    {
        $airport = $this->makeAirport();
        $airport->pullEvents();

        $airport->activate();
        $airport->pullEvents(); // clear activated event

        $airport->activate(); // second call – should be idempotent
        $events = $airport->pullEvents();

        $this->assertEmpty($events);
    }

    public function testDeactivateRegistersDeactivatedEvent(): void
    {
        $airport = $this->makeAirport();
        $airport->activate();
        $airport->pullEvents();

        $airport->deactivate();
        $events = $airport->pullEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(AirportDeactivated::class, $events[0]);
        $this->assertFalse($airport->isActive());
    }

    public function testDeactivateOnAlreadyInactiveAirportDoesNotRegisterDuplicateEvent(): void
    {
        $airport = $this->makeAirport();
        $airport->pullEvents();

        $airport->deactivate(); // already inactive – should be idempotent
        $events = $airport->pullEvents();

        $this->assertEmpty($events);
    }

    public function testAirportCanHaveGeoCoordinates(): void
    {
        $coords = new GeoCoordinates(50.4744, 19.0800);
        $airport = $this->makeAirport(geoCoordinates: $coords);

        $this->assertNotNull($airport->getGeoCoordinates());
        $this->assertSame(50.4744, $airport->getGeoCoordinates()?->getLatitude());
        $this->assertSame(19.0800, $airport->getGeoCoordinates()?->getLongitude());
    }

    public function testAirportCanBeCreatedWithoutGeoCoordinates(): void
    {
        $airport = $this->makeAirport();

        $this->assertNull($airport->getGeoCoordinates());
    }

    public function testGetIataCodeReturnsValueObject(): void
    {
        $airport = $this->makeAirport('WAW');

        $this->assertInstanceOf(IataCode::class, $airport->getIataCode());
        $this->assertTrue($airport->getIataCode()->equals(new IataCode('WAW')));
    }

    public function testFromPrimitivesRestoresState(): void
    {
        $airport = Airport::fromPrimitives(
            '550e8400-e29b-41d4-a716-446655440000',
            'WAW',
            'Warsaw Chopin Airport',
            'PL',
            'Warsaw',
            true,
        );

        $this->assertSame('WAW', $airport->getIataCode()->getValue());
        $this->assertTrue($airport->isActive());
        $this->assertEmpty($airport->pullEvents());
    }
}
