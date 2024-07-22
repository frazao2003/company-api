<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240721174526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_899e06f3979b1ad6 RENAME TO IDX_8B4747B7979B1AD6');
        $this->addSql('ALTER INDEX idx_899e06f39393f8fe RENAME TO IDX_8B4747B79393F8FE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER INDEX idx_8b4747b79393f8fe RENAME TO idx_899e06f39393f8fe');
        $this->addSql('ALTER INDEX idx_8b4747b7979b1ad6 RENAME TO idx_899e06f3979b1ad6');
    }
}
