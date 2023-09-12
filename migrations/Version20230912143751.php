<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230912143751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE delivery_option_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "order_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE order_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE delivery_option (id INT NOT NULL, name VARCHAR(255) NOT NULL, average_delivery_days SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "order" (id INT NOT NULL, delivery_option_id INT NOT NULL, status VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, estimated_delivery_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivery_address_line1 VARCHAR(255) NOT NULL, delivery_address_line2 VARCHAR(255) DEFAULT NULL, delivery_address_city VARCHAR(255) DEFAULT NULL, delivery_address_post_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F5299398E3A151FD ON "order" (delivery_option_id)');
        $this->addSql('COMMENT ON COLUMN "order".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "order".estimated_delivery_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE order_item (id INT NOT NULL, parent_order_id INT NOT NULL, identifier VARCHAR(255) NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_52EA1F091252C1E9 ON order_item (parent_order_id)');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398E3A151FD FOREIGN KEY (delivery_option_id) REFERENCES delivery_option (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F091252C1E9 FOREIGN KEY (parent_order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE delivery_option_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "order_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE order_item_id_seq CASCADE');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398E3A151FD');
        $this->addSql('ALTER TABLE order_item DROP CONSTRAINT FK_52EA1F091252C1E9');
        $this->addSql('DROP TABLE delivery_option');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_item');
    }
}
