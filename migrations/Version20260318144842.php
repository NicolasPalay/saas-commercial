<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318144842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_details ADD taxe_id INT DEFAULT NULL, DROP taxe');
        $this->addSql('ALTER TABLE invoice_details ADD CONSTRAINT FK_80FF3D591AB947A4 FOREIGN KEY (taxe_id) REFERENCES taxe (id)');
        $this->addSql('CREATE INDEX IDX_80FF3D591AB947A4 ON invoice_details (taxe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_details DROP FOREIGN KEY FK_80FF3D591AB947A4');
        $this->addSql('DROP INDEX IDX_80FF3D591AB947A4 ON invoice_details');
        $this->addSql('ALTER TABLE invoice_details ADD taxe DOUBLE PRECISION NOT NULL, DROP taxe_id');
    }
}
