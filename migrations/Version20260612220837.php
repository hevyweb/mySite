<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612220837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create new table for graves.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE grave (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, maiden_name VARCHAR(255) DEFAULT NULL, birth_date DATE DEFAULT NULL, death_date DATE NOT NULL, birth_place VARCHAR(255) DEFAULT NULL, birth_place_coordinates VARCHAR(515) DEFAULT NULL, death_place VARCHAR(255) DEFAULT NULL, death_place_coordinates VARCHAR(515) DEFAULT NULL, grave_place VARCHAR(255) DEFAULT NULL, grave_place_coordinates VARCHAR(515) DEFAULT NULL, epitaph VARCHAR(255) DEFAULT NULL, image VARCHAR(255) NOT NULL, is_published TINYINT(1) NOT NULL, gender INT NOT NULL, biography LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE grave');
    }
}
