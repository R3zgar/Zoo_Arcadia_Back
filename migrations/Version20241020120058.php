<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241020120058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Les colonnes 'first_name' et 'last_name' existent déjà, donc nous supprimons leur ajout
        // De même, l'index UNIQUE sur 'api_token' existe déjà, donc nous évitons de l'ajouter à nouveau
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6497BA2F5EB ON user (api_token)');
    }

    public function down(Schema $schema): void
    {
        // Cette méthode rétablit les changements effectués par up() si nécessaire
        $this->addSql('DROP INDEX UNIQ_8D93D6497BA2F5EB ON user');
        $this->addSql('ALTER TABLE user DROP first_name, DROP last_name');
    }
}
