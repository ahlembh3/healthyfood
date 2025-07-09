<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619191905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient ADD calories INT DEFAULT NULL, ADD proteines DOUBLE PRECISION DEFAULT NULL, ADD glucides DOUBLE PRECISION DEFAULT NULL, ADD lipides DOUBLE PRECISION DEFAULT NULL, ADD origine VARCHAR(255) DEFAULT NULL, ADD bio TINYINT(1) DEFAULT 0 NOT NULL, ADD image VARCHAR(255) DEFAULT NULL, ADD allergenes LONGTEXT DEFAULT NULL, ADD saisonnalite VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient DROP calories, DROP proteines, DROP glucides, DROP lipides, DROP origine, DROP bio, DROP image, DROP allergenes, DROP saisonnalite, CHANGE type type VARCHAR(50) NOT NULL
        SQL);
    }
}
