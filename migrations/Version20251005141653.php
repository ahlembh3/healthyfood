<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005141653 extends AbstractMigration
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
            CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', image VARCHAR(255) DEFAULT NULL, validation TINYINT(1) DEFAULT 0 NOT NULL, categorie VARCHAR(255) NOT NULL, source VARCHAR(255) DEFAULT NULL, INDEX IDX_23A0E66FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE bienfait (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, recette_id INT DEFAULT NULL, article_id INT DEFAULT NULL, utilisateur_id INT NOT NULL, signale_par_id INT DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', note INT DEFAULT NULL, signaler TINYINT(1) DEFAULT 0 NOT NULL, type SMALLINT NOT NULL, signale_le DATETIME DEFAULT NULL, INDEX IDX_67F068BC89312FE9 (recette_id), INDEX IDX_67F068BC7294869C (article_id), INDEX IDX_67F068BCFB88E14F (utilisateur_id), INDEX IDX_67F068BCAE190A20 (signale_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gene (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_F0FCA936C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gene_bienfait (gene_id INT NOT NULL, bienfait_id INT NOT NULL, INDEX IDX_1256E80738BEE1C3 (gene_id), INDEX IDX_1256E8075FE95C38 (bienfait_id), PRIMARY KEY(gene_id, bienfait_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, unite VARCHAR(255) NOT NULL, calories INT DEFAULT NULL, proteines DOUBLE PRECISION DEFAULT NULL, glucides DOUBLE PRECISION DEFAULT NULL, lipides DOUBLE PRECISION DEFAULT NULL, origine VARCHAR(255) DEFAULT NULL, bio TINYINT(1) DEFAULT 0 NOT NULL, image VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, allergenes LONGTEXT DEFAULT NULL, saisonnalite VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredient_gene (ingredient_id INT NOT NULL, gene_id INT NOT NULL, INDEX IDX_515E7A05933FE08C (ingredient_id), INDEX IDX_515E7A0538BEE1C3 (gene_id), PRIMARY KEY(ingredient_id, gene_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE plante (id INT AUTO_INCREMENT NOT NULL, nom_commun VARCHAR(255) NOT NULL, nom_scientifique VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, partie_utilisee VARCHAR(255) NOT NULL, precautions LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE plante_bienfait (plante_id INT NOT NULL, bienfait_id INT NOT NULL, INDEX IDX_F7AB8E56177B16E8 (plante_id), INDEX IDX_F7AB8E565FE95C38 (bienfait_id), PRIMARY KEY(plante_id, bienfait_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE recette (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, instructions LONGTEXT NOT NULL, temps_preparation INT DEFAULT NULL, difficulte VARCHAR(255) DEFAULT NULL, validation TINYINT(1) NOT NULL, portions INT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, temps_cuisson INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_49BB6390FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE recette_ingredient (id INT AUTO_INCREMENT NOT NULL, id_recette INT NOT NULL, id_ingredient INT NOT NULL, quantite DOUBLE PRECISION NOT NULL, INDEX IDX_17C041A99726CAE0 (id_recette), INDEX IDX_17C041A9CE25F8A7 (id_ingredient), UNIQUE INDEX uniq_recette_ingredient (id_recette, id_ingredient), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tisane (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, mode_preparation LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, dosage LONGTEXT DEFAULT NULL, precautions LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tisane_bienfait (tisane_id INT NOT NULL, bienfait_id INT NOT NULL, INDEX IDX_13E295F72930F991 (tisane_id), INDEX IDX_13E295F75FE95C38 (bienfait_id), PRIMARY KEY(tisane_id, bienfait_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tisane_plante (tisane_id INT NOT NULL, plante_id INT NOT NULL, INDEX IDX_A0A8F8E62930F991 (tisane_id), INDEX IDX_A0A8F8E6177B16E8 (plante_id), PRIMARY KEY(tisane_id, plante_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, preferences LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE accord_aromatique ADD CONSTRAINT FK_E38D4CF177B16E8 FOREIGN KEY (plante_id) REFERENCES plante (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE accord_aromatique ADD CONSTRAINT FK_E38D4CF933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT FK_23A0E66FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCAE190A20 FOREIGN KEY (signale_par_id) REFERENCES utilisateur (id)
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
        $this->addSql(<<<'SQL'
            ALTER TABLE plante_bienfait ADD CONSTRAINT FK_F7AB8E56177B16E8 FOREIGN KEY (plante_id) REFERENCES plante (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plante_bienfait ADD CONSTRAINT FK_F7AB8E565FE95C38 FOREIGN KEY (bienfait_id) REFERENCES bienfait (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette ADD CONSTRAINT FK_49BB6390FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient ADD CONSTRAINT FK_17C041A99726CAE0 FOREIGN KEY (id_recette) REFERENCES recette (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient ADD CONSTRAINT FK_17C041A9CE25F8A7 FOREIGN KEY (id_ingredient) REFERENCES ingredient (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tisane_bienfait ADD CONSTRAINT FK_13E295F72930F991 FOREIGN KEY (tisane_id) REFERENCES tisane (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tisane_bienfait ADD CONSTRAINT FK_13E295F75FE95C38 FOREIGN KEY (bienfait_id) REFERENCES bienfait (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tisane_plante ADD CONSTRAINT FK_A0A8F8E62930F991 FOREIGN KEY (tisane_id) REFERENCES tisane (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tisane_plante ADD CONSTRAINT FK_A0A8F8E6177B16E8 FOREIGN KEY (plante_id) REFERENCES plante (id) ON DELETE CASCADE
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
            ALTER TABLE article DROP FOREIGN KEY FK_23A0E66FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC89312FE9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC7294869C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCAE190A20
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
            ALTER TABLE plante_bienfait DROP FOREIGN KEY FK_F7AB8E56177B16E8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plante_bienfait DROP FOREIGN KEY FK_F7AB8E565FE95C38
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette DROP FOREIGN KEY FK_49BB6390FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient DROP FOREIGN KEY FK_17C041A99726CAE0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient DROP FOREIGN KEY FK_17C041A9CE25F8A7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tisane_bienfait DROP FOREIGN KEY FK_13E295F72930F991
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tisane_bienfait DROP FOREIGN KEY FK_13E295F75FE95C38
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tisane_plante DROP FOREIGN KEY FK_A0A8F8E62930F991
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tisane_plante DROP FOREIGN KEY FK_A0A8F8E6177B16E8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE accord_aromatique
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bienfait
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commentaire
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gene
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gene_bienfait
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ingredient
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ingredient_gene
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE plante
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE plante_bienfait
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recette
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recette_ingredient
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_request
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tisane
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tisane_bienfait
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tisane_plante
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
