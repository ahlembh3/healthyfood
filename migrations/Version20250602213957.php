<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602213957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

   public function up(Schema $schema): void
{
    // Étape 1 : ajouter la colonne NULLABLE temporairement
    $this->addSql('ALTER TABLE recette ADD created_at DATETIME DEFAULT NULL');

    // Étape 2 : mettre une date sur les anciennes recettes
    $this->addSql("UPDATE recette SET created_at = NOW() WHERE created_at IS NULL");

    // Étape 3 : rendre la colonne NON NULL
    $this->addSql("ALTER TABLE recette MODIFY created_at DATETIME NOT NULL");
}


    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE recette DROP image, DROP created_at
        SQL);
    }
}
