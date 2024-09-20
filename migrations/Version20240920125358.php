<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240920125358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etat (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fiche_frais (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, etat_id INT DEFAULT NULL, mois DATE NOT NULL, nb_justificatifs INT DEFAULT NULL, montant_valid NUMERIC(10, 2) DEFAULT NULL, date_modif DATETIME NOT NULL, INDEX IDX_5FC0A6A7A76ED395 (user_id), INDEX IDX_5FC0A6A7D5E86FF (etat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE frais_forfait (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, montant NUMERIC(5, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_frais_forfait (id INT AUTO_INCREMENT NOT NULL, fiches_frais_id INT DEFAULT NULL, frais_forfaits_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, date DATE NOT NULL, montant NUMERIC(10, 2) NOT NULL, quantite INT NOT NULL, INDEX IDX_BD293ECF439E7612 (fiches_frais_id), INDEX IDX_BD293ECF6BD9B1E1 (frais_forfaits_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_frais_hors_forfait (id INT AUTO_INCREMENT NOT NULL, fiches_frais_id INT DEFAULT NULL, INDEX IDX_EC01626D439E7612 (fiches_frais_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, cp VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, date_embauche DATE NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fiche_frais ADD CONSTRAINT FK_5FC0A6A7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fiche_frais ADD CONSTRAINT FK_5FC0A6A7D5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id)');
        $this->addSql('ALTER TABLE ligne_frais_forfait ADD CONSTRAINT FK_BD293ECF439E7612 FOREIGN KEY (fiches_frais_id) REFERENCES fiche_frais (id)');
        $this->addSql('ALTER TABLE ligne_frais_forfait ADD CONSTRAINT FK_BD293ECF6BD9B1E1 FOREIGN KEY (frais_forfaits_id) REFERENCES frais_forfait (id)');
        $this->addSql('ALTER TABLE ligne_frais_hors_forfait ADD CONSTRAINT FK_EC01626D439E7612 FOREIGN KEY (fiches_frais_id) REFERENCES fiche_frais (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_frais DROP FOREIGN KEY FK_5FC0A6A7A76ED395');
        $this->addSql('ALTER TABLE fiche_frais DROP FOREIGN KEY FK_5FC0A6A7D5E86FF');
        $this->addSql('ALTER TABLE ligne_frais_forfait DROP FOREIGN KEY FK_BD293ECF439E7612');
        $this->addSql('ALTER TABLE ligne_frais_forfait DROP FOREIGN KEY FK_BD293ECF6BD9B1E1');
        $this->addSql('ALTER TABLE ligne_frais_hors_forfait DROP FOREIGN KEY FK_EC01626D439E7612');
        $this->addSql('DROP TABLE etat');
        $this->addSql('DROP TABLE fiche_frais');
        $this->addSql('DROP TABLE frais_forfait');
        $this->addSql('DROP TABLE ligne_frais_forfait');
        $this->addSql('DROP TABLE ligne_frais_hors_forfait');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
