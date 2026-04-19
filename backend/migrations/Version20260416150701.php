<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416150701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE settings (timezone VARCHAR(64) DEFAULT \'UTC\' NOT NULL, week_start_day VARCHAR(255) NOT NULL, default_session_type VARCHAR(255) NOT NULL, default_session_minutes SMALLINT UNSIGNED DEFAULT 50 NOT NULL, default_break_minutes SMALLINT UNSIGNED DEFAULT 10 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, PRIMARY KEY (user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT FK_E545A0C5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_settings DROP FOREIGN KEY `FK_5C844C5A76ED395`');
        $this->addSql('DROP TABLE user_settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_settings (timezone VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT \'UTC\' NOT NULL COLLATE `utf8mb4_0900_ai_ci`, week_start_day VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, default_session_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, default_session_minutes SMALLINT UNSIGNED DEFAULT 50 NOT NULL, default_break_minutes SMALLINT UNSIGNED DEFAULT 10 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, PRIMARY KEY (user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_settings ADD CONSTRAINT `FK_5C844C5A76ED395` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY FK_E545A0C5A76ED395');
        $this->addSql('DROP TABLE settings');
    }
}
