<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250312083200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE speciality (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE tbl_internship ADD speciality_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D3B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7992FA9D3B5A08D7 ON tbl_internship (speciality_id)');
        $this->addSql('ALTER TABLE tbl_user ADD speciality_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tbl_user ADD CONSTRAINT FK_38B383A13B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_38B383A13B5A08D7 ON tbl_user (speciality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tbl_internship DROP CONSTRAINT FK_7992FA9D3B5A08D7');
        $this->addSql('ALTER TABLE tbl_user DROP CONSTRAINT FK_38B383A13B5A08D7');
        $this->addSql('DROP TABLE speciality');
        $this->addSql('DROP INDEX IDX_7992FA9D3B5A08D7');
        $this->addSql('ALTER TABLE tbl_internship DROP speciality_id');
        $this->addSql('DROP INDEX IDX_38B383A13B5A08D7');
        $this->addSql('ALTER TABLE tbl_user DROP speciality_id');
    }
}
