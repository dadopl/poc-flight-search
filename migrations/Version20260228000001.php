<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create airports table';
    }

    public function up(Schema $schema): void
    {
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

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE airports');
    }
}
