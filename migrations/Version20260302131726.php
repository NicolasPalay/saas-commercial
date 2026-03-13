<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302131726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ADD raison_social VARCHAR(255) NOT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD code_postal VARCHAR(255) DEFAULT NULL, ADD ville VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_details CHANGE quantity quantity DOUBLE PRECISION NOT NULL, CHANGE price_unit price_unit DOUBLE PRECISION NOT NULL, CHANGE price_total_ht price_total_ht DOUBLE PRECISION NOT NULL, CHANGE taxe taxe DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP raison_social, DROP address, DROP code_postal, DROP ville');
        $this->addSql('ALTER TABLE invoice_details CHANGE quantity quantity VARCHAR(255) NOT NULL, CHANGE price_unit price_unit VARCHAR(255) NOT NULL, CHANGE price_total_ht price_total_ht VARCHAR(255) NOT NULL, CHANGE taxe taxe VARCHAR(255) NOT NULL');
    }
}
