<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250906173306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE analytics_event (id BIGINT AUTO_INCREMENT NOT NULL, session_id BIGINT NOT NULL, type VARCHAR(32) NOT NULL, page_path VARCHAR(255) DEFAULT NULL, target VARCHAR(255) DEFAULT NULL, payload JSON DEFAULT NULL COMMENT '(DC2Type:json)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_9CD0310A613FECDF (session_id), INDEX idx_evt_type (type), INDEX idx_evt_path (page_path), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE analytics_favorite (id BIGINT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, aid VARCHAR(36) DEFAULT NULL, object_type VARCHAR(50) NOT NULL, object_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_326AF34EFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE analytics_page_view (id BIGINT AUTO_INCREMENT NOT NULL, session_id BIGINT NOT NULL, path VARCHAR(255) NOT NULL, entered_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', exited_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', duration_sec INT UNSIGNED NOT NULL, INDEX IDX_17081ECE613FECDF (session_id), INDEX idx_pv_path (path), INDEX idx_pv_entered (entered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE analytics_session (id BIGINT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, aid VARCHAR(36) NOT NULL, started_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', last_seen_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', landing_path VARCHAR(255) DEFAULT NULL, referrer VARCHAR(255) DEFAULT NULL, is_bounce TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_7AE6592AFB88E14F (utilisateur_id), INDEX idx_session_aid (aid), INDEX idx_session_started (started_at), INDEX idx_session_last_seen (last_seen_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE analytics_event ADD CONSTRAINT FK_9CD0310A613FECDF FOREIGN KEY (session_id) REFERENCES analytics_session (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE analytics_favorite ADD CONSTRAINT FK_326AF34EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE analytics_page_view ADD CONSTRAINT FK_17081ECE613FECDF FOREIGN KEY (session_id) REFERENCES analytics_session (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE analytics_session ADD CONSTRAINT FK_7AE6592AFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE analytics_event DROP FOREIGN KEY FK_9CD0310A613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE analytics_favorite DROP FOREIGN KEY FK_326AF34EFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE analytics_page_view DROP FOREIGN KEY FK_17081ECE613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE analytics_session DROP FOREIGN KEY FK_7AE6592AFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE analytics_event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE analytics_favorite
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE analytics_page_view
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE analytics_session
        SQL);
    }
}
