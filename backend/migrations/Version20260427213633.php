<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260427213633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Drop achievements tables only if they exist (they may not on a fresh DB)
        $this->addSql('DROP TABLE IF EXISTS user_achievements');
        $this->addSql('DROP TABLE IF EXISTS achievements');

        // Add streak columns if 'points' column exists (rename it), otherwise just add them
        $columns = $this->connection->fetchAllAssociative('SHOW COLUMNS FROM users');
        $columnNames = array_column($columns, 'Field');

        if (in_array('points', $columnNames, true) && !in_array('current_streak', $columnNames, true)) {
            $this->addSql('ALTER TABLE users ADD longest_streak INT DEFAULT 0 NOT NULL, CHANGE points current_streak INT DEFAULT 0 NOT NULL');
        } elseif (!in_array('current_streak', $columnNames, true)) {
            $this->addSql('ALTER TABLE users ADD current_streak INT DEFAULT 0 NOT NULL, ADD longest_streak INT DEFAULT 0 NOT NULL, ADD points INT DEFAULT 0 NOT NULL');
        } elseif (!in_array('longest_streak', $columnNames, true)) {
            $this->addSql('ALTER TABLE users ADD longest_streak INT DEFAULT 0 NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE achievements (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, points_required INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_achievements (id INT AUTO_INCREMENT NOT NULL, earned_at DATETIME NOT NULL, user_id INT NOT NULL, achievement_id INT NOT NULL, INDEX IDX_51EE02FCA76ED395 (user_id), INDEX IDX_51EE02FCB3EC99FE (achievement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_achievements ADD CONSTRAINT `FK_51EE02FCA76ED395` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_achievements ADD CONSTRAINT `FK_51EE02FCB3EC99FE` FOREIGN KEY (achievement_id) REFERENCES achievements (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD points INT DEFAULT 0 NOT NULL, DROP current_streak, DROP longest_streak');
    }
}
