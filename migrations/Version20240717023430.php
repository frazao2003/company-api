<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240717023430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE socio_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE socio (id INT NOT NULL, nome VARCHAR(255) NOT NULL, cpf VARCHAR(11) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN socio.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN socio.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE socio_companie (socio_id INT NOT NULL, companie_id INT NOT NULL, PRIMARY KEY(socio_id, companie_id))');
        $this->addSql('CREATE INDEX IDX_B1727347DA04E6A9 ON socio_companie (socio_id)');
        $this->addSql('CREATE INDEX IDX_B17273479DC4CE1F ON socio_companie (companie_id)');
        $this->addSql('ALTER TABLE socio_companie ADD CONSTRAINT FK_B1727347DA04E6A9 FOREIGN KEY (socio_id) REFERENCES socio (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio_companie ADD CONSTRAINT FK_B17273479DC4CE1F FOREIGN KEY (companie_id) REFERENCES companies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE socio_id_seq CASCADE');
        $this->addSql('ALTER TABLE socio_companie DROP CONSTRAINT FK_B1727347DA04E6A9');
        $this->addSql('ALTER TABLE socio_companie DROP CONSTRAINT FK_B17273479DC4CE1F');
        $this->addSql('DROP TABLE socio');
        $this->addSql('DROP TABLE socio_companie');
    }
}
