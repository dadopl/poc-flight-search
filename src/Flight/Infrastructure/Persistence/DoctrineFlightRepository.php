<?php

declare(strict_types=1);

namespace App\Flight\Infrastructure\Persistence;

use App\Airport\Domain\AirportId;
use App\Flight\Domain\Aggregate\Flight;
use App\Flight\Domain\Repository\FlightRepository;
use App\Flight\Domain\ValueObject\FlightId;
use App\Flight\Domain\ValueObject\FlightNumber;
use App\Flight\Domain\ValueObject\FlightStatus;
use App\Shared\Domain\ValueObject\DateTimeRange;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

final class DoctrineFlightRepository implements FlightRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findById(FlightId $id): ?Flight
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM flights WHERE id = :id',
            ['id' => $id->getValue()],
        );

        return $row !== false ? $this->fromRow($row) : null;
    }

    public function findByFlightNumber(FlightNumber $flightNumber): ?Flight
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM flights WHERE flight_number = :flightNumber',
            ['flightNumber' => $flightNumber->getValue()],
        );

        return $row !== false ? $this->fromRow($row) : null;
    }

    /** @return Flight[] */
    public function findByRoute(AirportId $departure, AirportId $arrival, DateTimeRange $range): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM flights
             WHERE departure_airport_id = :departure
               AND arrival_airport_id = :arrival
               AND departure_time >= :from
               AND departure_time <= :to',
            [
                'departure' => $departure->getValue(),
                'arrival'   => $arrival->getValue(),
                'from'      => $range->getFrom()->format('Y-m-d H:i:s'),
                'to'        => $range->getTo()->format('Y-m-d H:i:s'),
            ],
        );

        return array_map(fn (array $row) => $this->fromRow($row), $rows);
    }

    public function countByDepartureAirportAndDate(AirportId $airportId, DateTimeImmutable $date): int
    {
        $result = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM flights
             WHERE departure_airport_id = :airportId
               AND departure_time >= :from
               AND departure_time <= :to',
            [
                'airportId' => $airportId->getValue(),
                'from'      => $date->format('Y-m-d') . ' 00:00:00',
                'to'        => $date->format('Y-m-d') . ' 23:59:59',
            ],
        );

        return (int) $result;
    }

    /** @return Flight[] */
    public function findByStatus(?FlightStatus $status, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        if ($status === null) {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT * FROM flights ORDER BY departure_time ASC LIMIT :limit OFFSET :offset',
                ['limit' => $limit, 'offset' => $offset],
            );
        } else {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT * FROM flights WHERE status = :status ORDER BY departure_time ASC LIMIT :limit OFFSET :offset',
                ['status' => $status->value, 'limit' => $limit, 'offset' => $offset],
            );
        }

        return array_map(fn (array $row) => $this->fromRow($row), $rows);
    }

    public function save(Flight $flight): void
    {
        $data = [
            'flight_number'        => $flight->getFlightNumber()->getValue(),
            'departure_airport_id' => $flight->getDepartureAirportId()->getValue(),
            'arrival_airport_id'   => $flight->getArrivalAirportId()->getValue(),
            'departure_time'       => $flight->getDepartureTime()->format('Y-m-d H:i:s'),
            'arrival_time'         => $flight->getArrivalTime()->format('Y-m-d H:i:s'),
            'aircraft_model'       => $flight->getAircraft()->getModel(),
            'aircraft_total_seats' => $flight->getAircraft()->getTotalSeats(),
            'status'               => $flight->getStatus()->value,
        ];

        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM flights WHERE id = :id',
            ['id' => $flight->getId()->getValue()],
        );

        if ($exists !== false) {
            $this->connection->update('flights', $data, ['id' => $flight->getId()->getValue()]);
        } else {
            $this->connection->insert(
                'flights',
                array_merge(['id' => $flight->getId()->getValue()], $data),
            );
        }
    }

    public function delete(Flight $flight): void
    {
        $this->connection->delete('flights', ['id' => $flight->getId()->getValue()]);
    }

    /** @param array<string, mixed> $row */
    private function fromRow(array $row): Flight
    {
        return Flight::fromPrimitives(
            (string) $row['id'],
            (string) $row['flight_number'],
            (string) $row['departure_airport_id'],
            (string) $row['arrival_airport_id'],
            new DateTimeImmutable((string) $row['departure_time']),
            new DateTimeImmutable((string) $row['arrival_time']),
            (string) $row['aircraft_model'],
            (int) $row['aircraft_total_seats'],
            (string) $row['status'],
        );
    }
}
