<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617221152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE condolence (id INT AUTO_INCREMENT NOT NULL, grave_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', description LONGTEXT NOT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, INDEX IDX_78C12864E439654A (grave_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flower (id INT AUTO_INCREMENT NOT NULL, grave_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, INDEX IDX_A7D7C1DAE439654A (grave_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person_photo (id INT AUTO_INCREMENT NOT NULL, grave_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', filename VARCHAR(64) NOT NULL, description VARCHAR(255) DEFAULT NULL, shooting_date DATETIME DEFAULT NULL, INDEX IDX_B8819BDCE439654A (grave_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE condolence ADD CONSTRAINT FK_78C12864E439654A FOREIGN KEY (grave_id) REFERENCES grave (id)');
        $this->addSql('ALTER TABLE flower ADD CONSTRAINT FK_A7D7C1DAE439654A FOREIGN KEY (grave_id) REFERENCES grave (id)');
        $this->addSql('ALTER TABLE person_photo ADD CONSTRAINT FK_B8819BDCE439654A FOREIGN KEY (grave_id) REFERENCES grave (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE condolence DROP FOREIGN KEY FK_78C12864E439654A');
        $this->addSql('ALTER TABLE flower DROP FOREIGN KEY FK_A7D7C1DAE439654A');
        $this->addSql('ALTER TABLE person_photo DROP FOREIGN KEY FK_B8819BDCE439654A');
        $this->addSql('DROP TABLE condolence');
        $this->addSql('DROP TABLE flower');
        $this->addSql('DROP TABLE person_photo');
    }
}
