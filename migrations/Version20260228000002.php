<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rebuild airports table for full domain model (UUID PK, active flag, geo coordinates)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE airports');
        $this->addSql(
            'CREATE TABLE airports (
                id VARCHAR(36) NOT NULL,
                iata_code VARCHAR(3) NOT NULL,
                name VARCHAR(255) NOT NULL,
                country_code VARCHAR(2) NOT NULL,
                city VARCHAR(255) NOT NULL,
                active SMALLINT DEFAULT 0 NOT NULL,
                latitude DOUBLE PRECISION DEFAULT NULL,
                longitude DOUBLE PRECISION DEFAULT NULL,
                PRIMARY KEY(id),
                UNIQUE (iata_code)
            )'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE airports');
        $this->addSql(
            'CREATE TABLE airports (
                iata_code VARCHAR(3) NOT NULL,
                name VARCHAR(255) NOT NULL,
                city VARCHAR(255) NOT NULL,
                country_code VARCHAR(2) NOT NULL,
                PRIMARY KEY(iata_code)
            )'
        );
    }
}
