<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210309172711 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD trick_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1B281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id)');
        $this->addSql('CREATE INDEX IDX_64C19C1B281BE2E ON category (trick_id)');
        $this->addSql('ALTER TABLE user ADD trick_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649B281BE2E ON user (trick_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1B281BE2E');
        $this->addSql('DROP INDEX IDX_64C19C1B281BE2E ON category');
        $this->addSql('ALTER TABLE category DROP trick_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B281BE2E');
        $this->addSql('DROP INDEX IDX_8D93D649B281BE2E ON user');
        $this->addSql('ALTER TABLE user DROP trick_id');
    }
}
