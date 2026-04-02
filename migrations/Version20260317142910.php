<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260317142910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_detail (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, commande_id INT DEFAULT NULL, product_id INT DEFAULT NULL, taxe_id INT DEFAULT NULL, label VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, reduce DOUBLE PRECISION DEFAULT NULL, quantity DOUBLE PRECISION DEFAULT NULL, total DOUBLE PRECISION DEFAULT NULL, INDEX IDX_ED896F46979B1AD6 (company_id), INDEX IDX_ED896F4682EA2E54 (commande_id), INDEX IDX_ED896F464584665A (product_id), INDEX IDX_ED896F461AB947A4 (taxe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F46979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F4682EA2E54 FOREIGN KEY (commande_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F464584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F461AB947A4 FOREIGN KEY (taxe_id) REFERENCES taxe (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F46979B1AD6');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F4682EA2E54');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F464584665A');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F461AB947A4');
        $this->addSql('DROP TABLE order_detail');
    }
}
