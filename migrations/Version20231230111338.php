<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231230111338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "like_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "like" (id INT NOT NULL, advertisement_id INT NOT NULL, owner_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AC6340B3A1FBF71B ON "like" (advertisement_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B37E3C61F9 ON "like" (owner_id)');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B3A1FBF71B FOREIGN KEY (advertisement_id) REFERENCES advertisement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B37E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE "like_id_seq" CASCADE');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B3A1FBF71B');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B37E3C61F9');
        $this->addSql('DROP TABLE "like"');
    }
}
