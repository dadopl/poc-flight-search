<?php

declare(strict_types=1);

namespace App\Tests\Airport\Application\Query;

use App\Airport\Application\DTO\AirportResponse;
use App\Airport\Application\Query\GetAirportQuery;
use App\Airport\Application\Query\GetAirportQueryHandler;
use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportId;
use App\Airport\Domain\AirportName;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\City;
use App\Airport\Domain\Country;
use App\Airport\Domain\Exception\AirportNotFoundException;
use App\Airport\Domain\IataCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetAirportQueryHandlerTest extends TestCase
{
    private AirportRepository&MockObject $repository;
    private GetAirportQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AirportRepository::class);
        $this->handler = new GetAirportQueryHandler($this->repository);
    }

    public function testReturnsAirportResponse(): void
    {
        $airport = Airport::create(
            AirportId::generate(),
            new IataCode('WAW'),
            new AirportName('Warsaw Chopin Airport'),
            new Country('PL'),
            new City('Warsaw'),
        );

        $this->repository->method('findByIataCode')->willReturn($airport);

        $result = ($this->handler)(new GetAirportQuery('WAW'));

        $this->assertInstanceOf(AirportResponse::class, $result);
        $this->assertSame('WAW', $result->iataCode);
        $this->assertSame('Warsaw Chopin Airport', $result->name);
        $this->assertSame('PL', $result->country);
        $this->assertFalse($result->isActive);
    }

    public function testThrowsWhenAirportNotFound(): void
    {
        $this->repository->method('findByIataCode')->willReturn(null);

        $this->expectException(AirportNotFoundException::class);

        ($this->handler)(new GetAirportQuery('WAW'));
    }
}
