<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319125617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ADD total DOUBLE PRECISION DEFAULT NULL, ADD taxe DOUBLE PRECISION DEFAULT NULL, ADD total_ttc DOUBLE PRECISION DEFAULT NULL, DROP price_total_ht, DROP taxe_total, DROP price_total_ttc');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ADD price_total_ht DOUBLE PRECISION NOT NULL, ADD taxe_total DOUBLE PRECISION NOT NULL, ADD price_total_ttc DOUBLE PRECISION NOT NULL, DROP total, DROP taxe, DROP total_ttc');
    }
}
