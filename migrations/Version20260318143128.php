<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318143128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_details ADD product_id INT DEFAULT NULL, ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_details ADD CONSTRAINT FK_80FF3D594584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE invoice_details ADD CONSTRAINT FK_80FF3D59979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_80FF3D594584665A ON invoice_details (product_id)');
        $this->addSql('CREATE INDEX IDX_80FF3D59979B1AD6 ON invoice_details (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_details DROP FOREIGN KEY FK_80FF3D594584665A');
        $this->addSql('ALTER TABLE invoice_details DROP FOREIGN KEY FK_80FF3D59979B1AD6');
        $this->addSql('DROP INDEX IDX_80FF3D594584665A ON invoice_details');
        $this->addSql('DROP INDEX IDX_80FF3D59979B1AD6 ON invoice_details');
        $this->addSql('ALTER TABLE invoice_details DROP product_id, DROP company_id');
    }
}
