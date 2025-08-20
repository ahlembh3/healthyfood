<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250820165415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE accord_aromatique (id INT AUTO_INCREMENT NOT NULL, plante_id INT NOT NULL, ingredient_id INT DEFAULT NULL, ingredient_type VARCHAR(50) DEFAULT NULL, score DOUBLE PRECISION DEFAULT '1' NOT NULL, INDEX IDX_E38D4CF177B16E8 (plante_id), INDEX IDX_E38D4CF933FE08C (ingredient_id), UNIQUE INDEX u_pair_ing (plante_id, ingredient_id), UNIQUE INDEX u_pair_type (plante_id, ingredient_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gene (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_F0FCA936C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gene_bienfait (gene_id INT NOT NULL, bienfait_id INT NOT NULL, INDEX IDX_1256E80738BEE1C3 (gene_id), INDEX IDX_1256E8075FE95C38 (bienfait_id), PRIMARY KEY(gene_id, bienfait_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredient_gene (ingredient_id INT NOT NULL, gene_id INT NOT NULL, INDEX IDX_515E7A05933FE08C (ingredient_id), INDEX IDX_515E7A0538BEE1C3 (gene_id), PRIMARY KEY(ingredient_id, gene_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE accord_aromatique ADD CONSTRAINT FK_E38D4CF177B16E8 FOREIGN KEY (plante_id) REFERENCES plante (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE accord_aromatique ADD CONSTRAINT FK_E38D4CF933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gene_bienfait ADD CONSTRAINT FK_1256E80738BEE1C3 FOREIGN KEY (gene_id) REFERENCES gene (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gene_bienfait ADD CONSTRAINT FK_1256E8075FE95C38 FOREIGN KEY (bienfait_id) REFERENCES bienfait (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_gene ADD CONSTRAINT FK_515E7A05933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_gene ADD CONSTRAINT FK_515E7A0538BEE1C3 FOREIGN KEY (gene_id) REFERENCES gene (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE accord_aromatique DROP FOREIGN KEY FK_E38D4CF177B16E8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE accord_aromatique DROP FOREIGN KEY FK_E38D4CF933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gene_bienfait DROP FOREIGN KEY FK_1256E80738BEE1C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gene_bienfait DROP FOREIGN KEY FK_1256E8075FE95C38
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_gene DROP FOREIGN KEY FK_515E7A05933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_gene DROP FOREIGN KEY FK_515E7A0538BEE1C3
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE accord_aromatique
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gene
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gene_bienfait
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ingredient_gene
        SQL);
    }
}
