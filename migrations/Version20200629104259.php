<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200629104259 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE duty_type ADD creator_id INT DEFAULT NULL, ADD asked_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE duty_type ADD CONSTRAINT FK_20E523EC61220EA6 FOREIGN KEY (creator_id) REFERENCES member (id)');
        $this->addSql('CREATE INDEX IDX_20E523EC61220EA6 ON duty_type (creator_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE duty_type DROP FOREIGN KEY FK_20E523EC61220EA6');
        $this->addSql('DROP INDEX IDX_20E523EC61220EA6 ON duty_type');
        $this->addSql('ALTER TABLE duty_type DROP creator_id, DROP asked_at');
    }
}
