<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015122244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE veterinaire_rapport ADD animal_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE veterinaire_rapport ADD CONSTRAINT FK_C3B339768E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id)');
        $this->addSql('CREATE INDEX IDX_C3B339768E962C16 ON veterinaire_rapport (animal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE veterinaire_rapport DROP FOREIGN KEY FK_C3B339768E962C16');
        $this->addSql('DROP INDEX IDX_C3B339768E962C16 ON veterinaire_rapport');
        $this->addSql('ALTER TABLE veterinaire_rapport DROP animal_id');
    }
}
