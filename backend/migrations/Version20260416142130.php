<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416142130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE note_attachments (id INT AUTO_INCREMENT NOT NULL, original_filename VARCHAR(255) NOT NULL, storage_path VARCHAR(512) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, size_bytes INT UNSIGNED DEFAULT NULL, checksum_sha256 VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, note_id INT NOT NULL, INDEX idx_note_attachments_note_id (note_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE note_tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, note_id INT NOT NULL, tag_id INT NOT NULL, INDEX idx_note_tags_note_id (note_id), INDEX idx_note_tags_tag_id (tag_id), UNIQUE INDEX UNIQ_NOTETAG_NOTE_TAG (note_id, tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resource_tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, resource_id INT NOT NULL, tag_id INT NOT NULL, INDEX idx_resource_tags_resource_id (resource_id), INDEX idx_resource_tags_tag_id (tag_id), UNIQUE INDEX UNIQ_RESOURCETAG_RESOURCE_TAG (resource_id, tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resources (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, resource_type VARCHAR(255) NOT NULL, external_url VARCHAR(1024) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, storage_path VARCHAR(512) DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, size_bytes INT UNSIGNED DEFAULT NULL, checksum_sha256 VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, archived_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, subject_id INT DEFAULT NULL, INDEX idx_resources_user_id (user_id), INDEX idx_resources_subject_id (subject_id), INDEX idx_resources_resource_type (resource_type), INDEX idx_resources_archived_at (archived_at), INDEX idx_resources_deleted_at (deleted_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE subject_tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, subject_id INT NOT NULL, tag_id INT NOT NULL, INDEX idx_subject_tags_subject_id (subject_id), INDEX idx_subject_tags_tag_id (tag_id), UNIQUE INDEX UNIQ_SUBJECTTAG_SUBJECT_TAG (subject_id, tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, color_hex VARCHAR(7) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX idx_tags_user_id (user_id), UNIQUE INDEX UNIQ_TAG_USER_NAME (user_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE task_tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, task_id INT NOT NULL, tag_id INT NOT NULL, INDEX idx_task_tags_task_id (task_id), INDEX idx_task_tags_tag_id (tag_id), UNIQUE INDEX UNIQ_TASKTAG_TASK_TAG (task_id, tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE note_attachments ADD CONSTRAINT FK_617D6D5326ED0855 FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note_tags ADD CONSTRAINT FK_CFC6857E26ED0855 FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note_tags ADD CONSTRAINT FK_CFC6857EBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_tags ADD CONSTRAINT FK_609C349C89329D25 FOREIGN KEY (resource_id) REFERENCES resources (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource_tags ADD CONSTRAINT FK_609C349CBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resources ADD CONSTRAINT FK_EF66EBAEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resources ADD CONSTRAINT FK_EF66EBAE23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE subject_tags ADD CONSTRAINT FK_FD7610FD23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subject_tags ADD CONSTRAINT FK_FD7610FDBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tags ADD CONSTRAINT FK_6FBC9426A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_tags ADD CONSTRAINT FK_1C0F005D8DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_tags ADD CONSTRAINT FK_1C0F005DBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note_attachments DROP FOREIGN KEY FK_617D6D5326ED0855');
        $this->addSql('ALTER TABLE note_tags DROP FOREIGN KEY FK_CFC6857E26ED0855');
        $this->addSql('ALTER TABLE note_tags DROP FOREIGN KEY FK_CFC6857EBAD26311');
        $this->addSql('ALTER TABLE resource_tags DROP FOREIGN KEY FK_609C349C89329D25');
        $this->addSql('ALTER TABLE resource_tags DROP FOREIGN KEY FK_609C349CBAD26311');
        $this->addSql('ALTER TABLE resources DROP FOREIGN KEY FK_EF66EBAEA76ED395');
        $this->addSql('ALTER TABLE resources DROP FOREIGN KEY FK_EF66EBAE23EDC87');
        $this->addSql('ALTER TABLE subject_tags DROP FOREIGN KEY FK_FD7610FD23EDC87');
        $this->addSql('ALTER TABLE subject_tags DROP FOREIGN KEY FK_FD7610FDBAD26311');
        $this->addSql('ALTER TABLE tags DROP FOREIGN KEY FK_6FBC9426A76ED395');
        $this->addSql('ALTER TABLE task_tags DROP FOREIGN KEY FK_1C0F005D8DB60186');
        $this->addSql('ALTER TABLE task_tags DROP FOREIGN KEY FK_1C0F005DBAD26311');
        $this->addSql('DROP TABLE note_attachments');
        $this->addSql('DROP TABLE note_tags');
        $this->addSql('DROP TABLE resource_tags');
        $this->addSql('DROP TABLE resources');
        $this->addSql('DROP TABLE subject_tags');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE task_tags');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON users (email)');
    }
}
