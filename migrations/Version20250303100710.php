<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303100710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_list (id SERIAL NOT NULL, contained TEXT DEFAULT NULL, is_verified BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE company (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, zip INT DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, phone INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, is_verified BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE grade (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE internship (id SERIAL NOT NULL, intern_id INT DEFAULT NULL, school_id INT DEFAULT NULL, company_id INT DEFAULT NULL, activitylist_id INT DEFAULT NULL, visitreport_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, is_verified BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_10D1B00C525DD4B4 ON internship (intern_id)');
        $this->addSql('CREATE INDEX IDX_10D1B00CC32A47EE ON internship (school_id)');
        $this->addSql('CREATE INDEX IDX_10D1B00C979B1AD6 ON internship (company_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_10D1B00C96EB1108 ON internship (activitylist_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_10D1B00C60443FB7 ON internship (visitreport_id)');
        $this->addSql('CREATE TABLE school (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip INT DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, phone INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, grade_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93D649FE19A1A8 ON "user" (grade_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('CREATE TABLE visit_report (id SERIAL NOT NULL, contained TEXT DEFAULT NULL, is_verified BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE internship ADD CONSTRAINT FK_10D1B00C525DD4B4 FOREIGN KEY (intern_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE internship ADD CONSTRAINT FK_10D1B00CC32A47EE FOREIGN KEY (school_id) REFERENCES school (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE internship ADD CONSTRAINT FK_10D1B00C979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE internship ADD CONSTRAINT FK_10D1B00C96EB1108 FOREIGN KEY (activitylist_id) REFERENCES activity_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE internship ADD CONSTRAINT FK_10D1B00C60443FB7 FOREIGN KEY (visitreport_id) REFERENCES visit_report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649FE19A1A8 FOREIGN KEY (grade_id) REFERENCES grade (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE internship DROP CONSTRAINT FK_10D1B00C525DD4B4');
        $this->addSql('ALTER TABLE internship DROP CONSTRAINT FK_10D1B00CC32A47EE');
        $this->addSql('ALTER TABLE internship DROP CONSTRAINT FK_10D1B00C979B1AD6');
        $this->addSql('ALTER TABLE internship DROP CONSTRAINT FK_10D1B00C96EB1108');
        $this->addSql('ALTER TABLE internship DROP CONSTRAINT FK_10D1B00C60443FB7');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649FE19A1A8');
        $this->addSql('DROP TABLE activity_list');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE grade');
        $this->addSql('DROP TABLE internship');
        $this->addSql('DROP TABLE school');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE visit_report');
    }
}
