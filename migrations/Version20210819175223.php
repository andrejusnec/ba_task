<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210819175223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE query_list (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, address_record_id INT NOT NULL, send_status TINYINT(1) DEFAULT NULL, receive_status TINYINT(1) DEFAULT NULL, INDEX IDX_502B4C80F624B39D (sender_id), INDEX IDX_502B4C80CD53EDB6 (receiver_id), INDEX IDX_502B4C80D7BC4944 (address_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE query_list ADD CONSTRAINT FK_502B4C80F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE query_list ADD CONSTRAINT FK_502B4C80CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE query_list ADD CONSTRAINT FK_502B4C80D7BC4944 FOREIGN KEY (address_record_id) REFERENCES address_book (id)');
        $this->addSql('ALTER TABLE address_book ADD CONSTRAINT FK_B6A973DAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE query_list');
        $this->addSql('ALTER TABLE address_book DROP FOREIGN KEY FK_B6A973DAA76ED395');
    }
}
