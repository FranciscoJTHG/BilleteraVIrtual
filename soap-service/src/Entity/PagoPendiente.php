<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PagoPendienteRepository;

#[ORM\Entity(repositoryClass: PagoPendienteRepository::class)]
#[ORM\Table(name: 'pagos_pendientes', indexes: [
    new ORM\Index(name: 'idx_session', columns: ['sessionId']),
    new ORM\Index(name: 'idx_expiracion', columns: ['expiraEn'])
])]
class PagoPendiente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $sessionId;

    #[ORM\ManyToOne(targetEntity: Billetera::class, inversedBy: 'pagosPendientes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Billetera $billetera;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $monto;

    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $usado = false;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $estado = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fechaCreacion;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fechaExpiracion;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $fechaConfirmacion = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): static
    {
        $this->sessionId = $sessionId;
        return $this;
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

    public function getMonto(): string
    {
        return $this->monto;
    }

    public function setMonto(string $monto): static
    {
        $this->monto = $monto;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function isUsado(): bool
    {
        return $this->usado;
    }

    public function setUsado(bool $usado): static
    {
        $this->usado = $usado;
        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): static
    {
        $this->estado = $estado;
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

    public function getFechaCreacion(): \DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;
        return $this;
    }

    public function getFechaExpiracion(): \DateTimeInterface
    {
        return $this->fechaExpiracion;
    }

    public function setFechaExpiracion(\DateTimeInterface $fechaExpiracion): static
    {
        $this->fechaExpiracion = $fechaExpiracion;
        return $this;
    }

    public function getFechaConfirmacion(): ?\DateTimeInterface
    {
        return $this->fechaConfirmacion;
    }

    public function setFechaConfirmacion(?\DateTimeInterface $fechaConfirmacion): static
    {
        $this->fechaConfirmacion = $fechaConfirmacion;
        return $this;
    }

    public function isExpirado(): bool
    {
        return new \DateTime() > $this->fechaExpiracion;
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
}
