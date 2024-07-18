<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240717232801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE companies_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE socio_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE Partner_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE PartnerCompany_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE Partner (id INT NOT NULL, nome VARCHAR(255) NOT NULL, cpf VARCHAR(11) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN Partner.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN Partner.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE PartnerCompany (id INT NOT NULL, company_id INT NOT NULL, partner_id INT NOT NULL, percent DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_899E06F3979B1AD6 ON PartnerCompany (company_id)');
        $this->addSql('CREATE INDEX IDX_899E06F39393F8FE ON PartnerCompany (partner_id)');
        $this->addSql('CREATE TABLE company (id INT NOT NULL, nome_fantasia VARCHAR(255) NOT NULL, cnpj VARCHAR(14) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN company.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE PartnerCompany ADD CONSTRAINT FK_899E06F3979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE PartnerCompany ADD CONSTRAINT FK_899E06F39393F8FE FOREIGN KEY (partner_id) REFERENCES Partner (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio_companie DROP CONSTRAINT fk_b1727347da04e6a9');
        $this->addSql('ALTER TABLE socio_companie DROP CONSTRAINT fk_b17273479dc4ce1f');
        $this->addSql('DROP TABLE companies');
        $this->addSql('DROP TABLE socio_companie');
        $this->addSql('DROP TABLE socio');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE Partner_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE PartnerCompany_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE company_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE companies_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE socio_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE companies (id INT NOT NULL, nome_fantasia VARCHAR(255) NOT NULL, cnpj VARCHAR(14) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN companies.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE socio_companie (socio_id INT NOT NULL, companie_id INT NOT NULL, PRIMARY KEY(socio_id, companie_id))');
        $this->addSql('CREATE INDEX idx_b17273479dc4ce1f ON socio_companie (companie_id)');
        $this->addSql('CREATE INDEX idx_b1727347da04e6a9 ON socio_companie (socio_id)');
        $this->addSql('CREATE TABLE socio (id INT NOT NULL, nome VARCHAR(255) NOT NULL, cpf VARCHAR(11) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN socio.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN socio.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE socio_companie ADD CONSTRAINT fk_b1727347da04e6a9 FOREIGN KEY (socio_id) REFERENCES socio (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio_companie ADD CONSTRAINT fk_b17273479dc4ce1f FOREIGN KEY (companie_id) REFERENCES companies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE PartnerCompany DROP CONSTRAINT FK_899E06F3979B1AD6');
        $this->addSql('ALTER TABLE PartnerCompany DROP CONSTRAINT FK_899E06F39393F8FE');
        $this->addSql('DROP TABLE Partner');
        $this->addSql('DROP TABLE PartnerCompany');
        $this->addSql('DROP TABLE company');
    }
}
