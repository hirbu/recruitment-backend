<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327013003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id SERIAL NOT NULL, resume_id INT DEFAULT NULL, posting_id INT NOT NULL, name VARCHAR(255) NOT NULL, fields TEXT DEFAULT NULL, score INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A45BDDC1D262AF09 ON application (resume_id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC19AE985F6 ON application (posting_id)');
        $this->addSql('COMMENT ON COLUMN application.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN application.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE city (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE posting (id SERIAL NOT NULL, owner_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, fields TEXT NOT NULL, job_type VARCHAR(255) NOT NULL, experience_level VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BD275D737E3C61F9 ON posting (owner_id)');
        $this->addSql('COMMENT ON COLUMN posting.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN posting.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE posting_city (posting_id INT NOT NULL, city_id INT NOT NULL, PRIMARY KEY(posting_id, city_id))');
        $this->addSql('CREATE INDEX IDX_9B02BFDF9AE985F6 ON posting_city (posting_id)');
        $this->addSql('CREATE INDEX IDX_9B02BFDF8BAC62AF ON posting_city (city_id)');
        $this->addSql('CREATE TABLE posting_tag (posting_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(posting_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_3C1961BC9AE985F6 ON posting_tag (posting_id)');
        $this->addSql('CREATE INDEX IDX_3C1961BCBAD26311 ON posting_tag (tag_id)');
        $this->addSql('CREATE TABLE resume (id SERIAL NOT NULL, path TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tag (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC19AE985F6 FOREIGN KEY (posting_id) REFERENCES posting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posting ADD CONSTRAINT FK_BD275D737E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posting_city ADD CONSTRAINT FK_9B02BFDF9AE985F6 FOREIGN KEY (posting_id) REFERENCES posting (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posting_city ADD CONSTRAINT FK_9B02BFDF8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posting_tag ADD CONSTRAINT FK_3C1961BC9AE985F6 FOREIGN KEY (posting_id) REFERENCES posting (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE posting_tag ADD CONSTRAINT FK_3C1961BCBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_A45BDDC1D262AF09');
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_A45BDDC19AE985F6');
        $this->addSql('ALTER TABLE posting DROP CONSTRAINT FK_BD275D737E3C61F9');
        $this->addSql('ALTER TABLE posting_city DROP CONSTRAINT FK_9B02BFDF9AE985F6');
        $this->addSql('ALTER TABLE posting_city DROP CONSTRAINT FK_9B02BFDF8BAC62AF');
        $this->addSql('ALTER TABLE posting_tag DROP CONSTRAINT FK_3C1961BC9AE985F6');
        $this->addSql('ALTER TABLE posting_tag DROP CONSTRAINT FK_3C1961BCBAD26311');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE posting');
        $this->addSql('DROP TABLE posting_city');
        $this->addSql('DROP TABLE posting_tag');
        $this->addSql('DROP TABLE resume');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE "user"');
    }
}
