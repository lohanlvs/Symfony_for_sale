<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231206104344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE advertisement ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE advertisement ADD CONSTRAINT FK_C95F6AEE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C95F6AEE7E3C61F9 ON advertisement (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE advertisement DROP CONSTRAINT FK_C95F6AEE7E3C61F9');
        $this->addSql('DROP INDEX IDX_C95F6AEE7E3C61F9');
        $this->addSql('ALTER TABLE advertisement DROP owner_id');
    }
}
