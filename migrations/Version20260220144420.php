<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220144420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD taxe_id INT DEFAULT NULL, ADD stock DOUBLE PRECISION DEFAULT NULL, ADD is_active TINYINT(1) NOT NULL, ADD is_service TINYINT(1) NOT NULL, ADD cost_price NUMERIC(10, 2) DEFAULT NULL, ADD barcode VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD1AB947A4 FOREIGN KEY (taxe_id) REFERENCES taxe (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD1AB947A4 ON product (taxe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD1AB947A4');
        $this->addSql('DROP INDEX IDX_D34A04AD1AB947A4 ON product');
        $this->addSql('ALTER TABLE product DROP taxe_id, DROP stock, DROP is_active, DROP is_service, DROP cost_price, DROP barcode');
    }
}
