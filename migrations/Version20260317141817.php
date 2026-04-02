<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260317141817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, devis_id INT DEFAULT NULL, total DOUBLE PRECISION DEFAULT NULL, taxe DOUBLE PRECISION DEFAULT NULL, total_ttc DOUBLE PRECISION DEFAULT NULL, delivery_street VARCHAR(255) DEFAULT NULL, delivery_street2 VARCHAR(255) DEFAULT NULL, delivery_postal_code VARCHAR(20) DEFAULT NULL, delivery_city VARCHAR(255) DEFAULT NULL, delivery_label VARCHAR(255) DEFAULT NULL, delivery_phone VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F5299398979B1AD6 (company_id), INDEX IDX_F529939841DEFADA (devis_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939841DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398979B1AD6');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939841DEFADA');
        $this->addSql('DROP TABLE `order`');
    }
}
