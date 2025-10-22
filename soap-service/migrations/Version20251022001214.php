<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022001214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billeteras DROP INDEX IDX_7A73F44FDE734E51, ADD UNIQUE INDEX UNIQ_B7060128DE734E51 (cliente_id)');
        $this->addSql('DROP INDEX idx_saldo ON billeteras');
        $this->addSql('ALTER TABLE billeteras CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX idx_email ON clientes');
        $this->addSql('DROP INDEX idx_documento ON clientes');
        $this->addSql('DROP INDEX UNIQ_3B6F9B9A9F5411AE ON clientes');
        $this->addSql('ALTER TABLE clientes ADD tipo_documento VARCHAR(10) NOT NULL, ADD apellidos VARCHAR(255) NOT NULL, ADD fecha_registro DATETIME NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE documento numero_documento VARCHAR(20) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_50FE07D7FBA25858 ON clientes (numero_documento)');
        $this->addSql('ALTER TABLE clientes RENAME INDEX uniq_3b6f9b9ae7927c74 TO UNIQ_50FE07D7E7927C74');
        $this->addSql('ALTER TABLE pagos_pendientes DROP FOREIGN KEY FK_8B2E4B3D4D83F8');
        $this->addSql('DROP INDEX idx_session ON pagos_pendientes');
        $this->addSql('ALTER TABLE pagos_pendientes ADD usado TINYINT(1) DEFAULT 0 NOT NULL, ADD expira_en DATETIME NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP descripcion, DROP estado, DROP fecha_creacion, DROP fecha_expiracion, DROP fecha_confirmacion, CHANGE token token VARCHAR(6) NOT NULL');
        $this->addSql('ALTER TABLE pagos_pendientes ADD CONSTRAINT FK_CD92F458A434F1EE FOREIGN KEY (billetera_id) REFERENCES billeteras (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pagos_pendientes RENAME INDEX uniq_8b2e4b3d83e1689 TO UNIQ_CD92F458613FECDF');
        $this->addSql('ALTER TABLE pagos_pendientes RENAME INDEX idx_8b2e4b3d4d83f8 TO IDX_CD92F458A434F1EE');
        $this->addSql('ALTER TABLE transacciones DROP FOREIGN KEY FK_9677E4C9D4D83F8');
        $this->addSql('DROP INDEX idx_tipo_fecha ON transacciones');
        $this->addSql('ALTER TABLE transacciones ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP referencia, DROP estado, DROP fecha');
        $this->addSql('ALTER TABLE transacciones ADD CONSTRAINT FK_66C5ED5EA434F1EE FOREIGN KEY (billetera_id) REFERENCES billeteras (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transacciones RENAME INDEX idx_9677e4c9d4d83f8 TO IDX_66C5ED5EA434F1EE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP INDEX UNIQ_50FE07D7FBA25858 ON clientes');
        $this->addSql('ALTER TABLE clientes DROP tipo_documento, DROP apellidos, DROP fecha_registro, CHANGE created_at created_at DATETIME NOT NULL, CHANGE numero_documento documento VARCHAR(20) NOT NULL');
        $this->addSql('CREATE INDEX idx_email ON clientes (email)');
        $this->addSql('CREATE INDEX idx_documento ON clientes (documento)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B6F9B9A9F5411AE ON clientes (documento)');
        $this->addSql('ALTER TABLE clientes RENAME INDEX uniq_50fe07d7e7927c74 TO UNIQ_3B6F9B9AE7927C74');
        $this->addSql('ALTER TABLE transacciones DROP FOREIGN KEY FK_66C5ED5EA434F1EE');
        $this->addSql('ALTER TABLE transacciones ADD referencia VARCHAR(255) DEFAULT NULL, ADD estado VARCHAR(20) NOT NULL, ADD fecha DATETIME NOT NULL, DROP created_at');
        $this->addSql('ALTER TABLE transacciones ADD CONSTRAINT FK_9677E4C9D4D83F8 FOREIGN KEY (billetera_id) REFERENCES billeteras (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX idx_tipo_fecha ON transacciones (tipo, fecha)');
        $this->addSql('ALTER TABLE transacciones RENAME INDEX idx_66c5ed5ea434f1ee TO IDX_9677E4C9D4D83F8');
        $this->addSql('ALTER TABLE billeteras DROP INDEX UNIQ_B7060128DE734E51, ADD INDEX IDX_7A73F44FDE734E51 (cliente_id)');
        $this->addSql('ALTER TABLE billeteras CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('CREATE INDEX idx_saldo ON billeteras (saldo)');
        $this->addSql('ALTER TABLE pagos_pendientes DROP FOREIGN KEY FK_CD92F458A434F1EE');
        $this->addSql('ALTER TABLE pagos_pendientes ADD descripcion LONGTEXT DEFAULT NULL, ADD estado VARCHAR(20) NOT NULL, ADD fecha_expiracion DATETIME NOT NULL, ADD fecha_confirmacion DATETIME DEFAULT NULL, DROP usado, DROP created_at, CHANGE token token VARCHAR(6) DEFAULT NULL, CHANGE expira_en fecha_creacion DATETIME NOT NULL');
        $this->addSql('ALTER TABLE pagos_pendientes ADD CONSTRAINT FK_8B2E4B3D4D83F8 FOREIGN KEY (billetera_id) REFERENCES billeteras (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX idx_session ON pagos_pendientes (session_id)');
        $this->addSql('ALTER TABLE pagos_pendientes RENAME INDEX idx_cd92f458a434f1ee TO IDX_8B2E4B3D4D83F8');
        $this->addSql('ALTER TABLE pagos_pendientes RENAME INDEX uniq_cd92f458613fecdf TO UNIQ_8B2E4B3D83E1689');
    }
}
