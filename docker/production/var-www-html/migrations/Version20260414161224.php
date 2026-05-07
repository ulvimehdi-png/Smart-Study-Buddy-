<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414161224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notes (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, content LONGTEXT NOT NULL, source_type VARCHAR(255) NOT NULL, external_url VARCHAR(1024) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, archived_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, subject_id INT DEFAULT NULL, task_id INT DEFAULT NULL, INDEX idx_notes_user_id (user_id), INDEX idx_notes_subject_id (subject_id), INDEX idx_notes_task_id (task_id), INDEX idx_notes_source_type (source_type), INDEX idx_notes_archived_at (archived_at), INDEX idx_notes_deleted_at (deleted_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sessions (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, session_type VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME DEFAULT NULL, planned_minutes INT UNSIGNED DEFAULT NULL, actual_focus_minutes INT UNSIGNED DEFAULT NULL, break_minutes INT UNSIGNED DEFAULT 0 NOT NULL, distraction_count INT UNSIGNED DEFAULT 0 NOT NULL, session_note VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX idx_sessions_user_id (user_id), INDEX idx_sessions_status (status), INDEX idx_sessions_session_type (session_type), INDEX idx_sessions_started_at (started_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68C23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68C8DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sessions ADD CONSTRAINT FK_9A609D13A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CA76ED395');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68C23EDC87');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68C8DB60186');
        $this->addSql('ALTER TABLE sessions DROP FOREIGN KEY FK_9A609D13A76ED395');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE sessions');
    }
}
