<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190812081307 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE transaction ADD created_at DATE NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD INDEX `user_id` (`user_id`);');
        $this->addSql('ALTER TABLE transaction ADD INDEX `transaction_id` (`transaction_id`);');
        $this->addSql('ALTER TABLE transaction ADD UNIQUE `user_transaction` (`user_id`, `transaction_id`);;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE transaction DROP created_at');
        $this->addSql('ALTER TABLE transaction DROP index user_id');
        $this->addSql('ALTER TABLE transaction DROP index transaction_id');
        $this->addSql('ALTER TABLE transaction DROP index `user_transaction`');
    }
}
