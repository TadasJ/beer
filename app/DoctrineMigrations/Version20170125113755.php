<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170125113755 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE beer (id INT AUTO_INCREMENT NOT NULL, brewery_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_58F666ADD15C960 (brewery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE brewery (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geocode (id INT AUTO_INCREMENT NOT NULL, brewery_id INT DEFAULT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_C6773CE4D15C960 (brewery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE beer ADD CONSTRAINT FK_58F666ADD15C960 FOREIGN KEY (brewery_id) REFERENCES brewery (id)');
        $this->addSql('ALTER TABLE geocode ADD CONSTRAINT FK_C6773CE4D15C960 FOREIGN KEY (brewery_id) REFERENCES brewery (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE beer DROP FOREIGN KEY FK_58F666ADD15C960');
        $this->addSql('ALTER TABLE geocode DROP FOREIGN KEY FK_C6773CE4D15C960');
        $this->addSql('DROP TABLE beer');
        $this->addSql('DROP TABLE brewery');
        $this->addSql('DROP TABLE geocode');
    }
}
