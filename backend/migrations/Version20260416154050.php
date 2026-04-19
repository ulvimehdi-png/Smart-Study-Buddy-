<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416154050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE session_entries (id INT AUTO_INCREMENT NOT NULL, planned_minutes INT UNSIGNED DEFAULT NULL, actual_focus_minutes INT UNSIGNED DEFAULT NULL, entry_started_at DATETIME DEFAULT NULL, entry_ended_at DATETIME DEFAULT NULL, subject_name_snapshot VARCHAR(120) DEFAULT NULL, task_title_snapshot VARCHAR(255) DEFAULT NULL, note_title_snapshot VARCHAR(150) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, session_id INT NOT NULL, subject_id INT DEFAULT NULL, task_id INT DEFAULT NULL, note_id INT DEFAULT NULL, INDEX idx_session_entries_session_id (session_id), INDEX idx_session_entries_subject_id (subject_id), INDEX idx_session_entries_task_id (task_id), INDEX idx_session_entries_note_id (note_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE session_entries ADD CONSTRAINT FK_3F20319F613FECDF FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_entries ADD CONSTRAINT FK_3F20319F23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE session_entries ADD CONSTRAINT FK_3F20319F8DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE session_entries ADD CONSTRAINT FK_3F20319F26ED0855 FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_entries DROP FOREIGN KEY FK_3F20319F613FECDF');
        $this->addSql('ALTER TABLE session_entries DROP FOREIGN KEY FK_3F20319F23EDC87');
        $this->addSql('ALTER TABLE session_entries DROP FOREIGN KEY FK_3F20319F8DB60186');
        $this->addSql('ALTER TABLE session_entries DROP FOREIGN KEY FK_3F20319F26ED0855');
        $this->addSql('DROP TABLE session_entries');
    }
}
