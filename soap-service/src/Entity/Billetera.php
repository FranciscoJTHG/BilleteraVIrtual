<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BilleteraRepository;

#[ORM\Entity(repositoryClass: BilleteraRepository::class)]
#[ORM\Table(name: 'billeteras', indexes: [
    new ORM\Index(name: 'idx_saldo', columns: ['saldo'])
])]
class Billetera
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Cliente::class, inversedBy: 'billetera', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $saldo = '0.00';

    #[ORM\OneToMany(targetEntity: Transaccion::class, mappedBy: 'billetera', cascade: ['remove'])]
    private Collection $transacciones;

    #[ORM\OneToMany(targetEntity: PagoPendiente::class, mappedBy: 'billetera', cascade: ['remove'])]
    private Collection $pagosPendientes;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fechaCreacion;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->transacciones = new ArrayCollection();
        $this->pagosPendientes = new ArrayCollection();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCliente(): Cliente
    {
        return $this->cliente;
    }

    public function setCliente(Cliente $cliente): static
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function getSaldo(): string
    {
        return $this->saldo;
    }

    public function setSaldo(string $saldo): static
    {
        $this->saldo = $saldo;
        return $this;
    }

    public function sumarSaldo(string $cantidad): static
    {
        $this->saldo = (string)((float)$this->saldo + (float)$cantidad);
        return $this;
    }

    public function restarSaldo(string $cantidad): static
    {
        $this->saldo = (string)((float)$this->saldo - (float)$cantidad);
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

    public function getTransacciones(): Collection
    {
        return $this->transacciones;
    }

    public function addTransaccion(Transaccion $transaccion): static
    {
        if (!$this->transacciones->contains($transaccion)) {
            $this->transacciones->add($transaccion);
            $transaccion->setBilletera($this);
        }
        return $this;
    }

    public function removeTransaccion(Transaccion $transaccion): static
    {
        if ($this->transacciones->removeElement($transaccion)) {
            if ($transaccion->getBilletera() === $this) {
                $transaccion->setBilletera(null);
            }
        }
        return $this;
    }

    public function getPagosPendientes(): Collection
    {
        return $this->pagosPendientes;
    }

    public function addPagoPendiente(PagoPendiente $pagoPendiente): static
    {
        if (!$this->pagosPendientes->contains($pagoPendiente)) {
            $this->pagosPendientes->add($pagoPendiente);
            $pagoPendiente->setBilletera($this);
        }
        return $this;
    }

    public function removePagoPendiente(PagoPendiente $pagoPendiente): static
    {
        if ($this->pagosPendientes->removeElement($pagoPendiente)) {
            if ($pagoPendiente->getBilletera() === $this) {
                $pagoPendiente->setBilletera(null);
            }
        }
        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function updateTimestamp(): static
    {
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getFechaActualizacion(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
}
