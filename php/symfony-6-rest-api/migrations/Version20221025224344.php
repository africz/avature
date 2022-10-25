<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221025224344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE position (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(40) NOT NULL, salary INTEGER NOT NULL, country VARCHAR(40) NOT NULL)');
        $this->addSql('CREATE TABLE skills (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, position_id INTEGER NOT NULL, name VARCHAR(40) NOT NULL, CONSTRAINT FK_D5311670DD842E46 FOREIGN KEY (position_id) REFERENCES position (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D5311670DD842E46 ON skills (position_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE position');
        $this->addSql('DROP TABLE skills');
    }
}
