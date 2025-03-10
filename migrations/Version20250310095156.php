<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250310095156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tbl_activity_list (id SERIAL NOT NULL, contained TEXT DEFAULT NULL, is_verified BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tbl_company (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, zip INT DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, phone INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, is_verified BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tbl_grade (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tbl_internship (id SERIAL NOT NULL, intern_id INT DEFAULT NULL, school_id INT DEFAULT NULL, company_id INT DEFAULT NULL, visitreport_id INT DEFAULT NULL, activitylist_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, is_verified BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7992FA9D525DD4B4 ON tbl_internship (intern_id)');
        $this->addSql('CREATE INDEX IDX_7992FA9DC32A47EE ON tbl_internship (school_id)');
        $this->addSql('CREATE INDEX IDX_7992FA9D979B1AD6 ON tbl_internship (company_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7992FA9D60443FB7 ON tbl_internship (visitreport_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7992FA9D96EB1108 ON tbl_internship (activitylist_id)');
        $this->addSql('CREATE TABLE tbl_school (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip INT DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, phone INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tbl_user (id SERIAL NOT NULL, grade_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, is_verified BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_38B383A1FE19A1A8 ON tbl_user (grade_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON tbl_user (email)');
        $this->addSql('CREATE TABLE tbl_visit_report (id SERIAL NOT NULL, contained TEXT DEFAULT NULL, is_verified BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D525DD4B4 FOREIGN KEY (intern_id) REFERENCES tbl_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9DC32A47EE FOREIGN KEY (school_id) REFERENCES tbl_school (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D979B1AD6 FOREIGN KEY (company_id) REFERENCES tbl_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D60443FB7 FOREIGN KEY (visitreport_id) REFERENCES tbl_visit_report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D96EB1108 FOREIGN KEY (activitylist_id) REFERENCES tbl_activity_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tbl_user ADD CONSTRAINT FK_38B383A1FE19A1A8 FOREIGN KEY (grade_id) REFERENCES tbl_grade (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tbl_internship DROP CONSTRAINT FK_7992FA9D525DD4B4');
        $this->addSql('ALTER TABLE tbl_internship DROP CONSTRAINT FK_7992FA9DC32A47EE');
        $this->addSql('ALTER TABLE tbl_internship DROP CONSTRAINT FK_7992FA9D979B1AD6');
        $this->addSql('ALTER TABLE tbl_internship DROP CONSTRAINT FK_7992FA9D60443FB7');
        $this->addSql('ALTER TABLE tbl_internship DROP CONSTRAINT FK_7992FA9D96EB1108');
        $this->addSql('ALTER TABLE tbl_user DROP CONSTRAINT FK_38B383A1FE19A1A8');
        $this->addSql('DROP TABLE tbl_activity_list');
        $this->addSql('DROP TABLE tbl_company');
        $this->addSql('DROP TABLE tbl_grade');
        $this->addSql('DROP TABLE tbl_internship');
        $this->addSql('DROP TABLE tbl_school');
        $this->addSql('DROP TABLE tbl_user');
        $this->addSql('DROP TABLE tbl_visit_report');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
