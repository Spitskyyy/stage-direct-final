<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250425093559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tbl_activity_list ADD creator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tbl_activity_list ADD CONSTRAINT FK_5E9E468161220EA6 FOREIGN KEY (creator_id) REFERENCES tbl_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5E9E468161220EA6 ON tbl_activity_list (creator_id)');
        $this->addSql('ALTER TABLE tbl_company ADD creator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tbl_company ADD CONSTRAINT FK_14EC013B61220EA6 FOREIGN KEY (creator_id) REFERENCES tbl_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_14EC013B61220EA6 ON tbl_company (creator_id)');
        $this->addSql('ALTER TABLE tbl_internship ADD creator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D61220EA6 FOREIGN KEY (creator_id) REFERENCES tbl_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7992FA9D61220EA6 ON tbl_internship (creator_id)');
        $this->addSql('ALTER TABLE tbl_visit_report ADD creator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tbl_visit_report ADD CONSTRAINT FK_BCE92761220EA6 FOREIGN KEY (creator_id) REFERENCES tbl_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BCE92761220EA6 ON tbl_visit_report (creator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tbl_visit_report DROP CONSTRAINT FK_BCE92761220EA6');
        $this->addSql('DROP INDEX IDX_BCE92761220EA6');
        $this->addSql('ALTER TABLE tbl_visit_report DROP creator_id');
        $this->addSql('ALTER TABLE tbl_internship DROP CONSTRAINT FK_7992FA9D61220EA6');
        $this->addSql('DROP INDEX IDX_7992FA9D61220EA6');
        $this->addSql('ALTER TABLE tbl_internship DROP creator_id');
        $this->addSql('ALTER TABLE tbl_activity_list DROP CONSTRAINT FK_5E9E468161220EA6');
        $this->addSql('DROP INDEX IDX_5E9E468161220EA6');
        $this->addSql('ALTER TABLE tbl_activity_list DROP creator_id');
        $this->addSql('ALTER TABLE tbl_company DROP CONSTRAINT FK_14EC013B61220EA6');
        $this->addSql('DROP INDEX IDX_14EC013B61220EA6');
        $this->addSql('ALTER TABLE tbl_company DROP creator_id');
    }
}
