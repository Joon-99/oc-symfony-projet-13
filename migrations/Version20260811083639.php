<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260811083639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Detach orders from owner on account deletion: nullable owner_id, ON DELETE SET NULL, archive buyer name columns';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP CONSTRAINT fk_e52ffdee7e3c61f9');
        $this->addSql('ALTER TABLE orders ADD archive_buyer_first_name VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ADD archive_buyer_last_name VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ALTER owner_id DROP NOT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE7E3C61F9');
        $this->addSql('ALTER TABLE orders DROP archive_buyer_first_name');
        $this->addSql('ALTER TABLE orders DROP archive_buyer_last_name');
        $this->addSql('ALTER TABLE orders ALTER owner_id SET NOT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT fk_e52ffdee7e3c61f9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
