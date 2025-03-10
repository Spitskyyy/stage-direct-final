<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306104729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tbl_activity_list (id INT AUTO_INCREMENT NOT NULL, contained LONGTEXT DEFAULT NULL, is_verified TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, zip INT DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, phone INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_grade (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_internship (id INT AUTO_INCREMENT NOT NULL, intern_id INT DEFAULT NULL, school_id INT DEFAULT NULL, company_id INT DEFAULT NULL, visitreport_id INT DEFAULT NULL, activitylist_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, is_verified TINYINT(1) DEFAULT NULL, INDEX IDX_7992FA9D525DD4B4 (intern_id), INDEX IDX_7992FA9DC32A47EE (school_id), INDEX IDX_7992FA9D979B1AD6 (company_id), UNIQUE INDEX UNIQ_7992FA9D60443FB7 (visitreport_id), UNIQUE INDEX UNIQ_7992FA9D96EB1108 (activitylist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_school (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip INT DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, phone INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_user (id INT AUTO_INCREMENT NOT NULL, grade_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, INDEX IDX_38B383A1FE19A1A8 (grade_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_visit_report (id INT AUTO_INCREMENT NOT NULL, contained LONGTEXT DEFAULT NULL, is_verified TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D525DD4B4 FOREIGN KEY (intern_id) REFERENCES tbl_user (id)');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9DC32A47EE FOREIGN KEY (school_id) REFERENCES tbl_school (id)');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D979B1AD6 FOREIGN KEY (company_id) REFERENCES tbl_company (id)');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D60443FB7 FOREIGN KEY (visitreport_id) REFERENCES tbl_visit_report (id)');
        $this->addSql('ALTER TABLE tbl_internship ADD CONSTRAINT FK_7992FA9D96EB1108 FOREIGN KEY (activitylist_id) REFERENCES tbl_activity_list (id)');
        $this->addSql('ALTER TABLE tbl_user ADD CONSTRAINT FK_38B383A1FE19A1A8 FOREIGN KEY (grade_id) REFERENCES tbl_grade (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tbl_internship DROP FOREIGN KEY FK_7992FA9D525DD4B4');
        $this->addSql('ALTER TABLE tbl_internship DROP FOREIGN KEY FK_7992FA9DC32A47EE');
        $this->addSql('ALTER TABLE tbl_internship DROP FOREIGN KEY FK_7992FA9D979B1AD6');
        $this->addSql('ALTER TABLE tbl_internship DROP FOREIGN KEY FK_7992FA9D60443FB7');
        $this->addSql('ALTER TABLE tbl_internship DROP FOREIGN KEY FK_7992FA9D96EB1108');
        $this->addSql('ALTER TABLE tbl_user DROP FOREIGN KEY FK_38B383A1FE19A1A8');
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
