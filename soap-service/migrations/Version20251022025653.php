<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251022025653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update pagos_pendientes table with new fields for payment processing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pagos_pendientes ADD estado VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE pagos_pendientes ADD descripcion LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pagos_pendientes ADD fecha_confirmacion DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE pagos_pendientes ADD fecha_expiracion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE pagos_pendientes CHANGE token token VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE pagos_pendientes CHANGE expira_en fecha_creacion DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pagos_pendientes DROP COLUMN estado');
        $this->addSql('ALTER TABLE pagos_pendientes DROP COLUMN descripcion');
        $this->addSql('ALTER TABLE pagos_pendientes DROP COLUMN fecha_confirmacion');
        $this->addSql('ALTER TABLE pagos_pendientes DROP COLUMN fecha_expiracion');
        $this->addSql('ALTER TABLE pagos_pendientes CHANGE token token VARCHAR(6) NOT NULL');
        $this->addSql('ALTER TABLE pagos_pendientes CHANGE fecha_creacion expira_en DATETIME NOT NULL');
    }
}
