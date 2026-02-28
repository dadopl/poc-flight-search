<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create flight_availabilities and airport_daily_flight_limits tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE flight_availabilities (
                id VARCHAR(36) NOT NULL,
                flight_id VARCHAR(36) NOT NULL,
                cabin_class VARCHAR(10) NOT NULL,
                total_seats INT NOT NULL,
                booked_seats INT NOT NULL DEFAULT 0,
                blocked_seats INT NOT NULL DEFAULT 0,
                minimum_available_threshold INT NOT NULL DEFAULT 0,
                PRIMARY KEY(id),
                UNIQUE (flight_id, cabin_class)
            )',
        );

        $this->addSql(
            'CREATE TABLE airport_daily_flight_limits (
                iata_code VARCHAR(3) NOT NULL,
                daily_limit INT NOT NULL,
                PRIMARY KEY(iata_code)
            )',
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE flight_availabilities');
        $this->addSql('DROP TABLE airport_daily_flight_limits');
    }
}
