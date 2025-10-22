<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020235000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create wallet system tables: clientes, billeteras, transacciones, pagos_pendientes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clientes (id INT AUTO_INCREMENT NOT NULL, documento VARCHAR(20) NOT NULL, nombres VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, celular VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_3B6F9B9A9F5411AE (documento), UNIQUE INDEX UNIQ_3B6F9B9AE7927C74 (email), INDEX idx_documento (documento), INDEX idx_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billeteras (id INT AUTO_INCREMENT NOT NULL, cliente_id INT NOT NULL, saldo DECIMAL(10, 2) DEFAULT \'0.00\' NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7A73F44FDE734E51 (cliente_id), INDEX idx_saldo (saldo), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transacciones (id INT AUTO_INCREMENT NOT NULL, billetera_id INT NOT NULL, tipo VARCHAR(20) NOT NULL, monto DECIMAL(10, 2) NOT NULL, referencia VARCHAR(255) DEFAULT NULL, descripcion LONGTEXT DEFAULT NULL, estado VARCHAR(20) NOT NULL, fecha DATETIME NOT NULL, INDEX IDX_9677E4C9D4D83F8 (billetera_id), INDEX idx_tipo_fecha (tipo, fecha), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pagos_pendientes (id INT AUTO_INCREMENT NOT NULL, billetera_id INT NOT NULL, session_id VARCHAR(36) NOT NULL, monto DECIMAL(10, 2) NOT NULL, descripcion LONGTEXT DEFAULT NULL, token VARCHAR(6) DEFAULT NULL, estado VARCHAR(20) NOT NULL, fecha_creacion DATETIME NOT NULL, fecha_expiracion DATETIME NOT NULL, fecha_confirmacion DATETIME DEFAULT NULL, INDEX IDX_8B2E4B3D4D83F8 (billetera_id), UNIQUE INDEX UNIQ_8B2E4B3D83E1689 (session_id), INDEX idx_session (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billeteras ADD CONSTRAINT FK_7A73F44FDE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id)');
        $this->addSql('ALTER TABLE transacciones ADD CONSTRAINT FK_9677E4C9D4D83F8 FOREIGN KEY (billetera_id) REFERENCES billeteras (id)');
        $this->addSql('ALTER TABLE pagos_pendientes ADD CONSTRAINT FK_8B2E4B3D4D83F8 FOREIGN KEY (billetera_id) REFERENCES billeteras (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billeteras DROP FOREIGN KEY FK_7A73F44FDE734E51');
        $this->addSql('ALTER TABLE transacciones DROP FOREIGN KEY FK_9677E4C9D4D83F8');
        $this->addSql('ALTER TABLE pagos_pendientes DROP FOREIGN KEY FK_8B2E4B3D4D83F8');
        $this->addSql('DROP TABLE pagos_pendientes');
        $this->addSql('DROP TABLE transacciones');
        $this->addSql('DROP TABLE billeteras');
        $this->addSql('DROP TABLE clientes');
    }
}