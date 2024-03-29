<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230814173230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial DB data.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, old_email VARCHAR(255) NOT NULL, new_email VARCHAR(255) NOT NULL, old_confirmation_token VARCHAR(64) NOT NULL, new_confirmation_token VARCHAR(64) NOT NULL, old_email_confirm_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', new_email_confirm_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', completed TINYINT(1) NOT NULL, INDEX IDX_9A7A1884A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE remember_me_token (id INT AUTO_INCREMENT NOT NULL, series VARCHAR(88) NOT NULL, value VARCHAR(88) NOT NULL, last_used DATETIME NOT NULL, class VARCHAR(100) NOT NULL, username VARCHAR(200) NOT NULL, UNIQUE INDEX UNIQ_89FEBAD03A10012D (series), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(69) NOT NULL, label VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_57698A6A77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, updated_by_id INT DEFAULT NULL, first_name VARCHAR(32) NOT NULL, last_name VARCHAR(32) NOT NULL, birthday DATE DEFAULT NULL, sex INT DEFAULT NULL, username VARCHAR(32) NOT NULL, email VARCHAR(64) NOT NULL, password VARCHAR(64) NOT NULL, enabled TINYINT(1) NOT NULL, recovery VARCHAR(64) DEFAULT NULL, recovered_at DATETIME DEFAULT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, email_confirm VARCHAR(64) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE email_history ADD CONSTRAINT FK_9A7A1884A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql(<<<SQL
INSERT INTO `role`(code, label) VALUES 
('ROLE_ADMIN', 'Administrator'), 
('ROLE_USER', 'User')
SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_history DROP FOREIGN KEY FK_9A7A1884A76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649896DBBDE');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('DROP TABLE email_history');
        $this->addSql('DROP TABLE remember_me_token');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_role');
    }
}
