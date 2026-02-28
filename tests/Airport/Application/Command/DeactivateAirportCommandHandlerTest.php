<?php

declare(strict_types=1);

namespace App\Tests\Airport\Application\Command;

use App\Airport\Application\Command\DeactivateAirportCommand;
use App\Airport\Application\Command\DeactivateAirportCommandHandler;
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

class DeactivateAirportCommandHandlerTest extends TestCase
{
    private AirportRepository&MockObject $repository;
    private DeactivateAirportCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AirportRepository::class);
        $this->handler = new DeactivateAirportCommandHandler($this->repository);
    }

    public function testDeactivatesActiveAirport(): void
    {
        $airport = Airport::create(
            AirportId::generate(),
            new IataCode('KTW'),
            new AirportName('Katowice Airport'),
            new Country('PL'),
            new City('Katowice'),
        );
        $airport->activate();
        $airport->pullEvents();

        $this->repository->method('findByIataCode')->willReturn($airport);
        $this->repository->expects($this->once())->method('save')->with($airport);

        ($this->handler)(new DeactivateAirportCommand('KTW'));

        $this->assertFalse($airport->isActive());
    }

    public function testThrowsWhenAirportNotFound(): void
    {
        $this->repository->method('findByIataCode')->willReturn(null);

        $this->expectException(AirportNotFoundException::class);

        ($this->handler)(new DeactivateAirportCommand('KTW'));
    }
}
