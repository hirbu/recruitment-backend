<?php

declare(strict_types=1);

namespace migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324004317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE posting ADD fields TEXT NOT NULL');
        $this->addSql('ALTER TABLE posting DROP job_type');
        $this->addSql('ALTER TABLE posting DROP experience_level');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE posting ADD job_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE posting ADD experience_level VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE posting DROP fields');
    }
}
