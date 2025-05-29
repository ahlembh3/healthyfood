<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525192333 extends AbstractMigration
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
            ALTER TABLE plante_bienfait ADD CONSTRAINT FK_F7AB8E56177B16E8 FOREIGN KEY (plante_id) REFERENCES plante (id) ON DELETE CASCADE
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
            ALTER TABLE tisane_plante ADD CONSTRAINT FK_A0A8F8E6177B16E8 FOREIGN KEY (plante_id) REFERENCES plante (id) ON DELETE CASCADE
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
    }
}
