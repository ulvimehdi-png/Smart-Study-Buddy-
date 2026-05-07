<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414152833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(20) DEFAULT \'todo\' NOT NULL, priority VARCHAR(20) DEFAULT \'medium\' NOT NULL, estimated_minutes INT UNSIGNED DEFAULT NULL, due_at DATETIME DEFAULT NULL, scheduled_for DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, archived_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, subject_id INT DEFAULT NULL, INDEX idx_tasks_user_id (user_id), INDEX idx_tasks_subject_id (subject_id), INDEX idx_tasks_status (status), INDEX idx_tasks_priority (priority), INDEX idx_tasks_due_at (due_at), INDEX idx_tasks_scheduled_for (scheduled_for), INDEX idx_tasks_archived_at (archived_at), INDEX idx_tasks_deleted_at (deleted_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_5058659723EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597A76ED395');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_5058659723EDC87');
        $this->addSql('DROP TABLE tasks');
    }
}
