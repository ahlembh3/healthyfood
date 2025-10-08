<?php

declare(strict_types=1);

namespace migrations_backup;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806100327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE recette_ingredient (id_recette INT NOT NULL, id_ingredient INT NOT NULL, quantite DOUBLE PRECISION NOT NULL, INDEX IDX_17C041A99726CAE0 (id_recette), INDEX IDX_17C041A9CE25F8A7 (id_ingredient), PRIMARY KEY(id_recette, id_ingredient)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient ADD CONSTRAINT FK_17C041A99726CAE0 FOREIGN KEY (id_recette) REFERENCES recette (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient ADD CONSTRAINT FK_17C041A9CE25F8A7 FOREIGN KEY (id_ingredient) REFERENCES ingredient (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient DROP FOREIGN KEY FK_17C041A99726CAE0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient DROP FOREIGN KEY FK_17C041A9CE25F8A7
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recette_ingredient
        SQL);
    }
}
