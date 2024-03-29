<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240326094627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_translation (id INT AUTO_INCREMENT NOT NULL, article_id_id INT NOT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, preview LONGTEXT NOT NULL, hit INT NOT NULL, image VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, draft TINYINT(1) NOT NULL, INDEX IDX_2EEA2F088F3EC46 (article_id_id), INDEX IDX_2EEA2F08B03A8386 (created_by_id), INDEX IDX_2EEA2F08896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F088F3EC46 FOREIGN KEY (article_id_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F08B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F08896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article DROP tags');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F088F3EC46');
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F08B03A8386');
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F08896DBBDE');
        $this->addSql('DROP TABLE article_translation');
        $this->addSql('ALTER TABLE article ADD tags LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }
}
