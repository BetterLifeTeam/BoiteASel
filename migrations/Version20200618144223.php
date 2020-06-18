<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618144223 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, duty_id INT DEFAULT NULL, member1_id INT NOT NULL, member2_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_8A8E26E93A1F9EC1 (duty_id), INDEX IDX_8A8E26E98C1C655B (member1_id), INDEX IDX_8A8E26E99EA9CAB5 (member2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE duty (id INT AUTO_INCREMENT NOT NULL, duty_type_id INT NOT NULL, asker_id INT NOT NULL, offerer_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL, checked_at DATETIME DEFAULT NULL, asker_valid_at DATETIME DEFAULT NULL, offerer_valid_at DATETIME DEFAULT NULL, done_at DATETIME DEFAULT NULL, setback_at DATETIME DEFAULT NULL, duration DOUBLE PRECISION NOT NULL, place VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, price INT NOT NULL, yes_vote LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', no_vote LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', vote_commentary JSON DEFAULT NULL, INDEX IDX_A5B06099A8708D42 (duty_type_id), INDEX IDX_A5B06099CF34C66B (asker_id), INDEX IDX_A5B060996C1B2519 (offerer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE duty_type (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, hourly_price INT NOT NULL, status TINYINT(1) NOT NULL, no_vote LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', yes_vote LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', vote_commentary JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, role LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, money INT NOT NULL, address VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307FF624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, INDEX IDX_BF5476CA7597D3FE (member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E93A1F9EC1 FOREIGN KEY (duty_id) REFERENCES duty (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E98C1C655B FOREIGN KEY (member1_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E99EA9CAB5 FOREIGN KEY (member2_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE duty ADD CONSTRAINT FK_A5B06099A8708D42 FOREIGN KEY (duty_type_id) REFERENCES duty_type (id)');
        $this->addSql('ALTER TABLE duty ADD CONSTRAINT FK_A5B06099CF34C66B FOREIGN KEY (asker_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE duty ADD CONSTRAINT FK_A5B060996C1B2519 FOREIGN KEY (offerer_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E93A1F9EC1');
        $this->addSql('ALTER TABLE duty DROP FOREIGN KEY FK_A5B06099A8708D42');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E98C1C655B');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E99EA9CAB5');
        $this->addSql('ALTER TABLE duty DROP FOREIGN KEY FK_A5B06099CF34C66B');
        $this->addSql('ALTER TABLE duty DROP FOREIGN KEY FK_A5B060996C1B2519');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7597D3FE');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE duty');
        $this->addSql('DROP TABLE duty_type');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
    }
}
