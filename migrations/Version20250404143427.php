<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404143427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tbl_grade ADD school_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tbl_grade ADD CONSTRAINT FK_F73EE42BC32A47EE FOREIGN KEY (school_id) REFERENCES tbl_school (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F73EE42BC32A47EE ON tbl_grade (school_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tbl_grade DROP CONSTRAINT FK_F73EE42BC32A47EE');
        $this->addSql('DROP INDEX IDX_F73EE42BC32A47EE');
        $this->addSql('ALTER TABLE tbl_grade DROP school_id');
    }
}
