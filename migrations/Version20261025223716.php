<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261025223716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create new table for stats.';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
        CREATE TABLE stats (
            id INT AUTO_INCREMENT NOT NULL, 
            path VARCHAR(255) NOT NULL, 
            visitor_hash VARCHAR(64) NOT NULL, 
            device_type VARCHAR(50) DEFAULT NULL, 
            browser VARCHAR(100) DEFAULT NULL, 
            os VARCHAR(100) DEFAULT NULL, 
            language VARCHAR(10) DEFAULT NULL, 
            country VARCHAR(10) DEFAULT NULL, 
            city VARCHAR(100) DEFAULT NULL, 
            referer VARCHAR(500) DEFAULT NULL, 
            utm_source VARCHAR(100) DEFAULT NULL, 
            utm_medium VARCHAR(100) DEFAULT NULL, 
            utm_campaign VARCHAR(100) DEFAULT NULL, 
            ip_address VARCHAR(45) DEFAULT NULL, 
            user_agent VARCHAR(500) DEFAULT NULL, -- Increased from 255 to prevent truncation
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            hits INT NOT NULL,  
            INDEX idx_path_date (path, created_at), 
            INDEX idx_visitor_hash (visitor_hash), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE stats');
    }
}
