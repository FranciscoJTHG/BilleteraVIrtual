<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TransaccionRepository;

#[ORM\Entity(repositoryClass: TransaccionRepository::class)]
#[ORM\Table(name: 'transacciones', indexes: [
    new ORM\Index(name: 'idx_billetera', columns: ['billetera_id']),
    new ORM\Index(name: 'idx_tipo', columns: ['tipo']),
    new ORM\Index(name: 'idx_fecha', columns: ['createdAt'])
])]
class Transaccion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Billetera::class, inversedBy: 'transacciones')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Billetera $billetera;

    #[ORM\Column(type: 'string', length: 20)]
    private string $tipo; // 'recarga' o 'pago'

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $monto;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $referencia = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $estado = 'completada';

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $fecha = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBilletera(): Billetera
    {
        return $this->billetera;
    }

    public function setBilletera(Billetera $billetera): static
    {
        $this->billetera = $billetera;
        return $this;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        if (!in_array($tipo, ['recarga', 'pago'])) {
            throw new \InvalidArgumentException('Tipo debe ser "recarga" o "pago"');
        }
        $this->tipo = $tipo;
        return $this;
    }

    public function getMonto(): string
    {
        return $this->monto;
    }

    public function setMonto(string $monto): static
    {
        $this->monto = $monto;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getReferencia(): ?string
    {
        return $this->referencia;
    }

    public function setReferencia(?string $referencia): static
    {
        $this->referencia = $referencia;
        return $this;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;
        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;
        return $this;
    }
}
