<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260322102722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make experience image column nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE experience CHANGE image image VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE experience CHANGE image image VARCHAR(64) NOT NULL');
    }
}
