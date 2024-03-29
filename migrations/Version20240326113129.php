<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240326113129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66B03A8386');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66896DBBDE');
        $this->addSql('DROP INDEX IDX_23A0E66B03A8386 ON article');
        $this->addSql('DROP INDEX IDX_23A0E66896DBBDE ON article');
        $this->addSql('DROP INDEX slug_locale ON article');
        $this->addSql('ALTER TABLE article DROP created_by_id, DROP updated_by_id, DROP title, DROP locale, DROP body, DROP created_at, DROP updated_at, DROP draft, DROP image, DROP preview, DROP hit');
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F088F3EC46');
        $this->addSql('DROP INDEX IDX_2EEA2F088F3EC46 ON article_translation');
        $this->addSql('ALTER TABLE article_translation CHANGE article_id_id article_id INT NOT NULL');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F087294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('CREATE INDEX IDX_2EEA2F087294869C ON article_translation (article_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD created_by_id INT NOT NULL, ADD updated_by_id INT DEFAULT NULL, ADD title VARCHAR(255) NOT NULL, ADD locale VARCHAR(2) NOT NULL, ADD body LONGTEXT NOT NULL, ADD created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL, ADD draft TINYINT(1) NOT NULL, ADD image VARCHAR(255) DEFAULT NULL, ADD preview LONGTEXT NOT NULL, ADD hit INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_23A0E66B03A8386 ON article (created_by_id)');
        $this->addSql('CREATE INDEX IDX_23A0E66896DBBDE ON article (updated_by_id)');
        $this->addSql('CREATE UNIQUE INDEX slug_locale ON article (slug, locale)');
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F087294869C');
        $this->addSql('DROP INDEX IDX_2EEA2F087294869C ON article_translation');
        $this->addSql('ALTER TABLE article_translation CHANGE article_id article_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F088F3EC46 FOREIGN KEY (article_id_id) REFERENCES article (id)');
        $this->addSql('CREATE INDEX IDX_2EEA2F088F3EC46 ON article_translation (article_id_id)');
    }
}
