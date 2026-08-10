<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260810133214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make product_id in cart_items and order_items not nullable, add product_name to order_items';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_items ALTER product_id SET NOT NULL');
        $this->addSql('ALTER TABLE order_items DROP CONSTRAINT fk_62809db04584665a');
        $this->addSql('DROP INDEX idx_62809db04584665a');
        $this->addSql('ALTER TABLE order_items ADD product_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE order_items ALTER product_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_items ALTER product_id DROP NOT NULL');
        $this->addSql('ALTER TABLE order_items DROP product_name');
        $this->addSql('ALTER TABLE order_items ALTER product_id DROP NOT NULL');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT fk_62809db04584665a FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_62809db04584665a ON order_items (product_id)');
    }
}
