<?php

declare(strict_types=1);

namespace App\Airport\Infrastructure\Persistence;

use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportId;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\GeoCoordinates;
use App\Airport\Domain\IataCode;
use Doctrine\DBAL\Connection;

final class DoctrineAirportRepository implements AirportRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findById(AirportId $id): ?Airport
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM airports WHERE id = :id',
            ['id' => $id->getValue()],
        );

        return $row !== false ? $this->fromRow($row) : null;
    }

    public function findByIataCode(IataCode $iataCode): ?Airport
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM airports WHERE iata_code = :iataCode',
            ['iataCode' => $iataCode->getValue()],
        );

        return $row !== false ? $this->fromRow($row) : null;
    }

    /** @return Airport[] */
    public function findAllActive(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM airports WHERE active = 1',
        );

        return array_map(fn (array $row) => $this->fromRow($row), $rows);
    }

    public function save(Airport $airport): void
    {
        $data = [
            'iata_code'    => $airport->getIataCode()->getValue(),
            'name'         => $airport->getName()->getValue(),
            'country_code' => $airport->getCountry()->getValue(),
            'city'         => $airport->getCity()->getValue(),
            'active'       => $airport->isActive() ? 1 : 0,
            'latitude'     => $airport->getGeoCoordinates()?->getLatitude(),
            'longitude'    => $airport->getGeoCoordinates()?->getLongitude(),
        ];

        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM airports WHERE id = :id',
            ['id' => $airport->getId()->getValue()],
        );

        if ($exists !== false) {
            $this->connection->update('airports', $data, ['id' => $airport->getId()->getValue()]);
        } else {
            $this->connection->insert(
                'airports',
                array_merge(['id' => $airport->getId()->getValue()], $data),
            );
        }
    }

    public function delete(Airport $airport): void
    {
        $this->connection->delete('airports', ['id' => $airport->getId()->getValue()]);
    }

    /** @param array<string, mixed> $row */
    private function fromRow(array $row): Airport
    {
        $geoCoordinates = null;

        if (isset($row['latitude']) && isset($row['longitude'])) {
            $geoCoordinates = new GeoCoordinates(
                (float) $row['latitude'],
                (float) $row['longitude'],
            );
        }

        return Airport::fromPrimitives(
            (string) $row['id'],
            (string) $row['iata_code'],
            (string) $row['name'],
            (string) $row['country_code'],
            (string) $row['city'],
            (bool) $row['active'],
            $geoCoordinates,
        );
    }
}
