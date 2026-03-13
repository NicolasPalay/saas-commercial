<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302131007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice_details (id INT AUTO_INCREMENT NOT NULL, invoice_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, quantity VARCHAR(255) NOT NULL, price_unit VARCHAR(255) NOT NULL, price_total_ht VARCHAR(255) NOT NULL, taxe VARCHAR(255) NOT NULL, INDEX IDX_80FF3D592989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice_details ADD CONSTRAINT FK_80FF3D592989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_details DROP FOREIGN KEY FK_80FF3D592989F1FD');
        $this->addSql('DROP TABLE invoice_details');
    }
}
