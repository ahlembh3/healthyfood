<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707140446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD article_id INT DEFAULT NULL, ADD type SMALLINT NOT NULL, CHANGE recette_id recette_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC7294869C FOREIGN KEY (article_id) REFERENCES article (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_67F068BC7294869C ON commentaire (article_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette DROP valeurs_nutrition
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC7294869C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_67F068BC7294869C ON commentaire
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP article_id, DROP type, CHANGE recette_id recette_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette ADD valeurs_nutrition LONGTEXT DEFAULT NULL
        SQL);
    }
}
