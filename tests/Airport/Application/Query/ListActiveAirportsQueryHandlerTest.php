<?php

declare(strict_types=1);

namespace App\Tests\Airport\Application\Query;

use App\Airport\Application\DTO\AirportResponse;
use App\Airport\Application\Query\ListActiveAirportsQuery;
use App\Airport\Application\Query\ListActiveAirportsQueryHandler;
use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportId;
use App\Airport\Domain\AirportName;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\City;
use App\Airport\Domain\Country;
use App\Airport\Domain\IataCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListActiveAirportsQueryHandlerTest extends TestCase
{
    private AirportRepository&MockObject $repository;
    private ListActiveAirportsQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AirportRepository::class);
        $this->handler = new ListActiveAirportsQueryHandler($this->repository);
    }

    public function testReturnsListOfActiveAirports(): void
    {
        $airport1 = Airport::create(
            AirportId::generate(),
            new IataCode('KTW'),
            new AirportName('Katowice Airport'),
            new Country('PL'),
            new City('Katowice'),
        );
        $airport1->activate();

        $airport2 = Airport::create(
            AirportId::generate(),
            new IataCode('WAW'),
            new AirportName('Warsaw Chopin Airport'),
            new Country('PL'),
            new City('Warsaw'),
        );
        $airport2->activate();

        $this->repository->method('findAllActive')->willReturn([$airport1, $airport2]);

        $result = ($this->handler)(new ListActiveAirportsQuery());

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(AirportResponse::class, $result);
        $this->assertSame('KTW', $result[0]->iataCode);
        $this->assertSame('WAW', $result[1]->iataCode);
    }

    public function testReturnsEmptyArrayWhenNoActiveAirports(): void
    {
        $this->repository->method('findAllActive')->willReturn([]);

        $result = ($this->handler)(new ListActiveAirportsQuery());

        $this->assertSame([], $result);
    }
}
