<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250905165620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout PK auto sur recette_ingredient + index unique + nettoyage des quantités NULL';
    }

    public function up(Schema $schema): void
    {
        // (Auto) : ajustements divers
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire CHANGE contenu contenu LONGTEXT DEFAULT NULL, CHANGE signaler signaler TINYINT(1) DEFAULT 0 NOT NULL
        SQL);

        // (Auto) : nouvelle PK auto-incrémentée
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)
        SQL);

        // (Auto) : garantir l’unicité (recette, ingredient)
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_recette_ingredient ON recette_ingredient (id_recette, id_ingredient)
        SQL);

        // ✅ Nettoyage des anciennes lignes : éviter les NULL sur quantite
        $this->addSql(<<<'SQL'
            UPDATE recette_ingredient
            SET quantite = 100
            WHERE quantite IS NULL
        SQL);

        // (OPTIONNEL) Si tu veux imposer un DEFAULT côté DB (peut créer du "bruit" de diff) :
        // $this->addSql("ALTER TABLE recette_ingredient MODIFY quantite DOUBLE NOT NULL DEFAULT 100");
    }

    public function down(Schema $schema): void
    {
        // (Auto) : revert des modifs
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE signaler signaler TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient MODIFY id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_recette_ingredient ON recette_ingredient
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON recette_ingredient
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient DROP id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient ADD PRIMARY KEY (id_recette, id_ingredient)
        SQL);
    }
}
