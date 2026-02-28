<?php

declare(strict_types=1);

namespace App\Availability\Infrastructure\Persistence;

use App\Availability\Domain\Aggregate\FlightAvailability;
use App\Availability\Domain\Repository\FlightAvailabilityRepository;
use App\Availability\Domain\ValueObject\AvailabilityId;
use App\Availability\Domain\ValueObject\CabinClass;
use Doctrine\DBAL\Connection;

final class DoctrineFlightAvailabilityRepository implements FlightAvailabilityRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findById(AvailabilityId $id): ?FlightAvailability
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM flight_availabilities WHERE id = :id',
            ['id' => $id->getValue()],
        );

        return $row !== false ? $this->fromRow($row) : null;
    }

    public function findByFlightAndCabin(string $flightId, CabinClass $cabinClass): ?FlightAvailability
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM flight_availabilities WHERE flight_id = :flightId AND cabin_class = :cabinClass',
            ['flightId' => $flightId, 'cabinClass' => $cabinClass->value],
        );

        return $row !== false ? $this->fromRow($row) : null;
    }

    /** @return FlightAvailability[] */
    public function findByFlightId(string $flightId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM flight_availabilities WHERE flight_id = :flightId',
            ['flightId' => $flightId],
        );

        return array_map(fn (array $row) => $this->fromRow($row), $rows);
    }

    public function save(FlightAvailability $availability): void
    {
        $data = [
            'flight_id'                   => $availability->getFlightId(),
            'cabin_class'                 => $availability->getCabinClass()->value,
            'total_seats'                 => $availability->getTotalSeats(),
            'booked_seats'                => $availability->getBookedSeats(),
            'blocked_seats'               => $availability->getBlockedSeats(),
            'minimum_available_threshold' => $availability->getMinimumAvailableThreshold(),
        ];

        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM flight_availabilities WHERE id = :id',
            ['id' => $availability->getId()->getValue()],
        );

        if ($exists !== false) {
            $this->connection->update(
                'flight_availabilities',
                $data,
                ['id' => $availability->getId()->getValue()],
            );
        } else {
            $this->connection->insert(
                'flight_availabilities',
                array_merge(['id' => $availability->getId()->getValue()], $data),
            );
        }
    }

    public function delete(FlightAvailability $availability): void
    {
        $this->connection->delete(
            'flight_availabilities',
            ['id' => $availability->getId()->getValue()],
        );
    }

    /** @param array<string, mixed> $row */
    private function fromRow(array $row): FlightAvailability
    {
        return FlightAvailability::fromPrimitives(
            (string) $row['id'],
            (string) $row['flight_id'],
            (string) $row['cabin_class'],
            (int) $row['total_seats'],
            (int) $row['booked_seats'],
            (int) $row['blocked_seats'],
            (int) $row['minimum_available_threshold'],
        );
    }
}
