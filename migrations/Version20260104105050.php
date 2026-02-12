<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104105050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devis (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, user_id INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, INDEX IDX_8B27C52B979B1AD6 (company_id), INDEX IDX_8B27C52BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE devis_details (id INT AUTO_INCREMENT NOT NULL, devis_id INT DEFAULT NULL, product_id INT DEFAULT NULL, taxe_id INT DEFAULT NULL, label VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, reduce DOUBLE PRECISION DEFAULT NULL, quantity DOUBLE PRECISION DEFAULT NULL, total DOUBLE PRECISION DEFAULT NULL, INDEX IDX_E0C890D641DEFADA (devis_id), INDEX IDX_E0C890D64584665A (product_id), INDEX IDX_E0C890D61AB947A4 (taxe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE taxe (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, contry_code VARCHAR(10) NOT NULL, rate DOUBLE PRECISION NOT NULL, type VARCHAR(255) NOT NULL, legal_code VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BA76ED395 FOREIGN KEY (user_id) REFERENCES `saas_user` (id)');
        $this->addSql('ALTER TABLE devis_details ADD CONSTRAINT FK_E0C890D641DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
        $this->addSql('ALTER TABLE devis_details ADD CONSTRAINT FK_E0C890D64584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE devis_details ADD CONSTRAINT FK_E0C890D61AB947A4 FOREIGN KEY (taxe_id) REFERENCES taxe (id)');
        $this->addSql('ALTER TABLE quote_details DROP FOREIGN KEY FK_9DC9A30E4584665A');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF4979B1AD6');
        $this->addSql('DROP TABLE quote_details');
        $this->addSql('DROP TABLE quote');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quote_details (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, reference VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, quantity INT DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, discount DOUBLE PRECISION DEFAULT NULL, INDEX IDX_9DC9A30E4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE quote (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, number_quote INT NOT NULL, INDEX IDX_6B71CBF4979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE quote_details ADD CONSTRAINT FK_9DC9A30E4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF4979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B979B1AD6');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BA76ED395');
        $this->addSql('ALTER TABLE devis_details DROP FOREIGN KEY FK_E0C890D641DEFADA');
        $this->addSql('ALTER TABLE devis_details DROP FOREIGN KEY FK_E0C890D64584665A');
        $this->addSql('ALTER TABLE devis_details DROP FOREIGN KEY FK_E0C890D61AB947A4');
        $this->addSql('DROP TABLE devis');
        $this->addSql('DROP TABLE devis_details');
        $this->addSql('DROP TABLE taxe');
    }
}
