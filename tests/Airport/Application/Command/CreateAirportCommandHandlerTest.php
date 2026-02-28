<?php

declare(strict_types=1);

namespace App\Tests\Airport\Application\Command;

use App\Airport\Application\Command\CreateAirportCommand;
use App\Airport\Application\Command\CreateAirportCommandHandler;
use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateAirportCommandHandlerTest extends TestCase
{
    private AirportRepository&MockObject $repository;
    private CreateAirportCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AirportRepository::class);
        $this->handler = new CreateAirportCommandHandler($this->repository);
    }

    public function testCreatesAndSavesAirport(): void
    {
        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Airport::class));

        ($this->handler)(new CreateAirportCommand(
            iataCode: 'KTW',
            name: 'Katowice Airport',
            country: 'PL',
            city: 'Katowice',
        ));
    }

    public function testCreatesAirportWithGeoCoordinates(): void
    {
        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Airport $airport): bool {
                return $airport->getGeoCoordinates() !== null
                    && $airport->getGeoCoordinates()->getLatitude() === 50.4744
                    && $airport->getGeoCoordinates()->getLongitude() === 19.0800;
            }));

        ($this->handler)(new CreateAirportCommand(
            iataCode: 'KTW',
            name: 'Katowice Airport',
            country: 'PL',
            city: 'Katowice',
            latitude: 50.4744,
            longitude: 19.0800,
        ));
    }

    public function testThrowsOnInvalidIataCode(): void
    {
        $this->expectException(\DomainException::class);

        ($this->handler)(new CreateAirportCommand(
            iataCode: 'invalid',
            name: 'Test Airport',
            country: 'PL',
            city: 'Katowice',
        ));
    }
}
