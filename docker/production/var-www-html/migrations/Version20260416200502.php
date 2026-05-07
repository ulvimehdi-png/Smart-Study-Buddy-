<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416200502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE settings CHANGE timezone timezone VARCHAR(64) DEFAULT \'Brussels/Europe\' NOT NULL');
        $this->addSql('ALTER TABLE tasks CHANGE status status VARCHAR(20) NOT NULL, CHANGE priority priority VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE settings CHANGE timezone timezone VARCHAR(64) DEFAULT \'UTC\' NOT NULL');
        $this->addSql('ALTER TABLE tasks CHANGE status status VARCHAR(20) DEFAULT \'todo\' NOT NULL, CHANGE priority priority VARCHAR(20) DEFAULT \'medium\' NOT NULL');
    }
}
