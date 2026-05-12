<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511233637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE achievements (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, points_required INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE note_attachments (id INT AUTO_INCREMENT NOT NULL, original_filename VARCHAR(255) NOT NULL, storage_path VARCHAR(512) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, size_bytes INT UNSIGNED DEFAULT NULL, checksum_sha256 VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, note_id INT NOT NULL, INDEX idx_note_attachments_note_id (note_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE note_tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, note_id INT NOT NULL, tag_id INT NOT NULL, INDEX idx_note_tags_note_id (note_id), INDEX idx_note_tags_tag_id (tag_id), UNIQUE INDEX UNIQ_NOTETAG_NOTE_TAG (note_id, tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notes (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, content LONGTEXT NOT NULL, source_type VARCHAR(255) NOT NULL, external_url VARCHAR(1024) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, archived_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, subject_id INT DEFAULT NULL, task_id INT DEFAULT NULL, INDEX idx_notes_user_id (user_id), INDEX idx_notes_subject_id (subject_id), INDEX idx_notes_task_id (task_id), INDEX idx_notes_source_type (source_type), INDEX idx_notes_archived_at (archived_at), INDEX idx_notes_deleted_at (deleted_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resource_tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, resource_id INT NOT NULL, tag_id INT NOT NULL, INDEX idx_resource_tags_resource_id (resource_id), INDEX idx_resource_tags_tag_id (tag_id), UNIQUE INDEX UNIQ_RESOURCETAG_RESOURCE_TAG (resource_id, tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resources (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, resource_type VARCHAR(255) NOT NULL, external_url VARCHAR(1024) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, storage_path VARCHAR(512) DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, size_bytes INT UNSIGNED DEFAULT NULL, checksum_sha256 VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, archived_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, subject_id INT DEFAULT NULL, INDEX idx_resources_user_id (user_id), INDEX idx_resources_subject_id (subject_id), INDEX idx_resources_resource_type (resource_type), INDEX idx_resources_archived_at (archived_at), INDEX idx_resources_deleted_at (deleted_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE session_entries (id INT AUTO_INCREMENT NOT NULL, planned_minutes INT UNSIGNED DEFAULT NULL, actual_focus_minutes INT UNSIGNED DEFAULT NULL, entry_started_at DATETIME DEFAULT NULL, entry_ended_at DATETIME DEFAULT NULL, subject_name_snapshot VARCHAR(120) DEFAULT NULL, task_title_snapshot VARCHAR(255) DEFAULT NULL, note_title_snapshot VARCHAR(150) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, session_id INT NOT NULL, subject_id INT DEFAULT NULL, task_id INT DEFAULT NULL, note_id INT DEFAULT NULL, INDEX idx_session_entries_session_id (session_id), INDEX idx_session_entries_subject_id (subject_id), INDEX idx_session_entries_task_id (task_id), INDEX idx_session_entries_note_id (note_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sessions (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, session_type VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME DEFAULT NULL, planned_minutes INT UNSIGNED DEFAULT NULL, actual_focus_minutes INT UNSIGNED DEFAULT NULL, break_minutes INT UNSIGNED DEFAULT 0 NOT NULL, distraction_count INT UNSIGNED DEFAULT 0 NOT NULL, session_note VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX idx_sessions_user_id (user_id), INDEX idx_sessions_status (status), INDEX idx_sessions_session_type (session_type), INDEX idx_sessions_started_at (started_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE settings (timezone VARCHAR(64) DEFAULT \'Europe/Brussels\' NOT NULL, week_start_day VARCHAR(255) NOT NULL, default_session_type VARCHAR(255) NOT NULL, default_session_minutes SMALLINT UNSIGNED DEFAULT 50 NOT NULL, default_break_minutes SMALLINT UNSIGNED DEFAULT 10 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, PRIMARY KEY (user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE subject_tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, subject_id INT NOT NULL, tag_id INT NOT NULL, INDEX idx_subject_tags_subject_id (subject_id), INDEX idx_subject_tags_tag_id (tag_id), UNIQUE INDEX UNIQ_SUBJECTTAG_SUBJECT_TAG (subject_id, tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE subjects (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(32) DEFAULT NULL, name VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, color_hex VARCHAR(7) DEFAULT NULL, target_total_minutes INT UNSIGNED DEFAULT NULL, target_weekly_minutes INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, archived_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX idx_subjects_user_id (user_id), INDEX idx_subjects_archived_at (archived_at), INDEX idx_subjects_deleted_at (deleted_at), UNIQUE INDEX UNIQ_IDENTIFIER_USER_CODE (user_id, code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, color_hex VARCHAR(7) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX idx_tags_user_id (user_id), UNIQUE INDEX UNIQ_TAG_USER_NAME (user_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE task_tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, task_id INT NOT NULL, tag_id INT NOT NULL, INDEX idx_task_tags_task_id (task_id), INDEX idx_task_tags_tag_id (tag_id), UNIQUE INDEX UNIQ_TASKTAG_TASK_TAG (task_id, tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, priority VARCHAR(20) NOT NULL, estimated_minutes INT UNSIGNED DEFAULT NULL, due_at DATETIME DEFAULT NULL, scheduled_for DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, archived_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, subject_id INT DEFAULT NULL, INDEX idx_tasks_user_id (user_id), INDEX idx_tasks_subject_id (subject_id), INDEX idx_tasks_status (status), INDEX idx_tasks_priority (priority), INDEX idx_tasks_due_at (due_at), INDEX idx_tasks_scheduled_for (scheduled_for), INDEX idx_tasks_archived_at (archived_at), INDEX idx_tasks_deleted_at (deleted_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_achievements (id INT AUTO_INCREMENT NOT NULL, earned_at DATETIME NOT NULL, user_id INT NOT NULL, achievement_id INT NOT NULL, INDEX IDX_51EE02FCA76ED395 (user_id), INDEX IDX_51EE02FCB3EC99FE (achievement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(180) DEFAULT NULL, roles JSON NOT NULL, hashed_password VARCHAR(255) NOT NULL, display_name VARCHAR(120) DEFAULT NULL, profile_image_path VARCHAR(255) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, last_login_at DATETIME DEFAULT NULL, current_streak INT DEFAULT 0 NOT NULL, longest_streak INT DEFAULT 0 NOT NULL, points INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE note_attachments ADD CONSTRAINT FK_617D6D5326ED0855 FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note_tags ADD CONSTRAINT FK_CFC6857E26ED0855 FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note_tags ADD CONSTRAINT FK_CFC6857EBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68C23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68C8DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE resource_tags ADD CONSTRAINT FK_609C349C89329D25 FOREIGN KEY (resource_id) REFERENCES resources (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_tags ADD CONSTRAINT FK_609C349CBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resources ADD CONSTRAINT FK_EF66EBAEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resources ADD CONSTRAINT FK_EF66EBAE23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE session_entries ADD CONSTRAINT FK_3F20319F613FECDF FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_entries ADD CONSTRAINT FK_3F20319F23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE session_entries ADD CONSTRAINT FK_3F20319F8DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE session_entries ADD CONSTRAINT FK_3F20319F26ED0855 FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sessions ADD CONSTRAINT FK_9A609D13A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT FK_E545A0C5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subject_tags ADD CONSTRAINT FK_FD7610FD23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subject_tags ADD CONSTRAINT FK_FD7610FDBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subjects ADD CONSTRAINT FK_AB259917A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tags ADD CONSTRAINT FK_6FBC9426A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_tags ADD CONSTRAINT FK_1C0F005D8DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_tags ADD CONSTRAINT FK_1C0F005DBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_5058659723EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_achievements ADD CONSTRAINT FK_51EE02FCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_achievements ADD CONSTRAINT FK_51EE02FCB3EC99FE FOREIGN KEY (achievement_id) REFERENCES achievements (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note_attachments DROP FOREIGN KEY FK_617D6D5326ED0855');
        $this->addSql('ALTER TABLE note_tags DROP FOREIGN KEY FK_CFC6857E26ED0855');
        $this->addSql('ALTER TABLE note_tags DROP FOREIGN KEY FK_CFC6857EBAD26311');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CA76ED395');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68C23EDC87');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68C8DB60186');
        $this->addSql('ALTER TABLE resource_tags DROP FOREIGN KEY FK_609C349C89329D25');
        $this->addSql('ALTER TABLE resource_tags DROP FOREIGN KEY FK_609C349CBAD26311');
        $this->addSql('ALTER TABLE resources DROP FOREIGN KEY FK_EF66EBAEA76ED395');
        $this->addSql('ALTER TABLE resources DROP FOREIGN KEY FK_EF66EBAE23EDC87');
        $this->addSql('ALTER TABLE session_entries DROP FOREIGN KEY FK_3F20319F613FECDF');
        $this->addSql('ALTER TABLE session_entries DROP FOREIGN KEY FK_3F20319F23EDC87');
        $this->addSql('ALTER TABLE session_entries DROP FOREIGN KEY FK_3F20319F8DB60186');
        $this->addSql('ALTER TABLE session_entries DROP FOREIGN KEY FK_3F20319F26ED0855');
        $this->addSql('ALTER TABLE sessions DROP FOREIGN KEY FK_9A609D13A76ED395');
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY FK_E545A0C5A76ED395');
        $this->addSql('ALTER TABLE subject_tags DROP FOREIGN KEY FK_FD7610FD23EDC87');
        $this->addSql('ALTER TABLE subject_tags DROP FOREIGN KEY FK_FD7610FDBAD26311');
        $this->addSql('ALTER TABLE subjects DROP FOREIGN KEY FK_AB259917A76ED395');
        $this->addSql('ALTER TABLE tags DROP FOREIGN KEY FK_6FBC9426A76ED395');
        $this->addSql('ALTER TABLE task_tags DROP FOREIGN KEY FK_1C0F005D8DB60186');
        $this->addSql('ALTER TABLE task_tags DROP FOREIGN KEY FK_1C0F005DBAD26311');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597A76ED395');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_5058659723EDC87');
        $this->addSql('ALTER TABLE user_achievements DROP FOREIGN KEY FK_51EE02FCA76ED395');
        $this->addSql('ALTER TABLE user_achievements DROP FOREIGN KEY FK_51EE02FCB3EC99FE');
        $this->addSql('DROP TABLE achievements');
        $this->addSql('DROP TABLE note_attachments');
        $this->addSql('DROP TABLE note_tags');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE resource_tags');
        $this->addSql('DROP TABLE resources');
        $this->addSql('DROP TABLE session_entries');
        $this->addSql('DROP TABLE sessions');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE subject_tags');
        $this->addSql('DROP TABLE subjects');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE task_tags');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE user_achievements');
        $this->addSql('DROP TABLE users');
    }
}
