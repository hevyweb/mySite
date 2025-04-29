<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429210734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make field nullable to support autosave.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_translation CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE body body LONGTEXT DEFAULT NULL, CHANGE preview preview LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_translation CHANGE title title VARCHAR(255) NOT NULL, CHANGE body body LONGTEXT NOT NULL, CHANGE preview preview LONGTEXT NOT NULL');
    }
}
