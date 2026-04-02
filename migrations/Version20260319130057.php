<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319130057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_details ADD price DOUBLE PRECISION DEFAULT NULL, ADD total DOUBLE PRECISION DEFAULT NULL, DROP price_unit, DROP price_total_ht, CHANGE quantity quantity DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_details ADD price_unit DOUBLE PRECISION NOT NULL, ADD price_total_ht DOUBLE PRECISION NOT NULL, DROP price, DROP total, CHANGE quantity quantity DOUBLE PRECISION NOT NULL');
    }
}
