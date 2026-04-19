<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414150934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subjects (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(32) DEFAULT NULL, name VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, color_hex VARCHAR(7) DEFAULT NULL, target_total_minutes INT UNSIGNED DEFAULT NULL, target_weekly_minutes INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, archived_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX idx_subjects_user_id (user_id), INDEX idx_subjects_archived_at (archived_at), INDEX idx_subjects_deleted_at (deleted_at), UNIQUE INDEX UNIQ_IDENTIFIER_USER_CODE (user_id, code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE subjects ADD CONSTRAINT FK_AB259917A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subjects DROP FOREIGN KEY FK_AB259917A76ED395');
        $this->addSql('DROP TABLE subjects');
    }
}
