<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250516094011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // SUBTASKS
        $this->addSql('CREATE TABLE subtasks (
                id BIGINT AUTO_INCREMENT NOT NULL,
                task_id BIGINT NOT NULL,
                title VARCHAR(255) NOT NULL,
                is_completed TINYINT(1) DEFAULT 0,
                INDEX IDX_SUBTASKS_TASK_ID (task_id),
                PRIMARY KEY(id),
                CONSTRAINT FK_SUBTASKS_TASK_ID FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'DROP TABLE subtasks'
        );
    }
}
