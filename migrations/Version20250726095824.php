<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726095824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE plante_bienfait (plante_id INT NOT NULL, bienfait_id INT NOT NULL, INDEX IDX_F7AB8E56177B16E8 (plante_id), INDEX IDX_F7AB8E565FE95C38 (bienfait_id), PRIMARY KEY(plante_id, bienfait_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tisane_bienfait (tisane_id INT NOT NULL, bienfait_id INT NOT NULL, INDEX IDX_13E295F72930F991 (tisane_id), INDEX IDX_13E295F75FE95C38 (bienfait_id), PRIMARY KEY(tisane_id, bienfait_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tisane_plante (tisane_id INT NOT NULL, plante_id INT NOT NULL, INDEX IDX_A0A8F8E62930F991 (tisane_id), INDEX IDX_A0A8F8E6177B16E8 (plante_id), PRIMARY KEY(tisane_id, plante_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plante_bienfait ADD CONSTRAINT FK_F7AB8E56177B16E8 FOREIGN KEY (plante_id) REFERENCES plantes (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plante_bienfait ADD CONSTRAINT FK_F7AB8E565FE95C38 FOREIGN KEY (bienfait_id) REFERENCES bienfait (id) ON DELETE CASCADE
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
            ALTER TABLE tisane_plante ADD CONSTRAINT FK_A0A8F8E6177B16E8 FOREIGN KEY (plante_id) REFERENCES plantes (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD article_id INT DEFAULT NULL, ADD signale_par_id INT DEFAULT NULL, ADD type SMALLINT NOT NULL, ADD signale_le DATETIME DEFAULT NULL, CHANGE recette_id recette_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC7294869C FOREIGN KEY (article_id) REFERENCES article (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCAE190A20 FOREIGN KEY (signale_par_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_67F068BC7294869C ON commentaire (article_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_67F068BCAE190A20 ON commentaire (signale_par_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient ADD calories INT DEFAULT NULL, ADD proteines DOUBLE PRECISION DEFAULT NULL, ADD glucides DOUBLE PRECISION DEFAULT NULL, ADD lipides DOUBLE PRECISION DEFAULT NULL, ADD origine VARCHAR(255) DEFAULT NULL, ADD bio TINYINT(1) DEFAULT 0 NOT NULL, ADD image VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(255) DEFAULT NULL, ADD allergenes LONGTEXT DEFAULT NULL, ADD saisonnalite VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette ADD temps_cuisson INT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', DROP valeurs_nutrition, CHANGE image image VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE plante_bienfait DROP FOREIGN KEY FK_F7AB8E56177B16E8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plante_bienfait DROP FOREIGN KEY FK_F7AB8E565FE95C38
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
            DROP TABLE plante_bienfait
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tisane_bienfait
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tisane_plante
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC7294869C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCAE190A20
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_67F068BC7294869C ON commentaire
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_67F068BCAE190A20 ON commentaire
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP article_id, DROP signale_par_id, DROP type, DROP signale_le, CHANGE recette_id recette_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient DROP calories, DROP proteines, DROP glucides, DROP lipides, DROP origine, DROP bio, DROP image, DROP type, DROP allergenes, DROP saisonnalite
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette ADD valeurs_nutrition LONGTEXT DEFAULT NULL, DROP temps_cuisson, DROP created_at, CHANGE image image VARCHAR(255) NOT NULL
        SQL);
    }
}
