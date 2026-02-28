<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create flights table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE flights (
                id VARCHAR(36) NOT NULL,
                flight_number VARCHAR(6) NOT NULL,
                departure_airport_id VARCHAR(36) NOT NULL,
                arrival_airport_id VARCHAR(36) NOT NULL,
                departure_time DATETIME NOT NULL,
                arrival_time DATETIME NOT NULL,
                aircraft_model VARCHAR(255) NOT NULL,
                aircraft_total_seats INT NOT NULL,
                status VARCHAR(20) NOT NULL,
                PRIMARY KEY(id),
                UNIQUE (flight_number)
            )',
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE flights');
    }
}
