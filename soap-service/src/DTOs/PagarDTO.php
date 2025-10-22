<?php

namespace App\DTOs;

use Symfony\Component\Validator\Constraints as Assert;

class PagarDTO
{
    #[Assert\NotBlank(message: 'El cliente ID es requerido')]
    #[Assert\GreaterThan(value: 0, message: 'El cliente ID debe ser mayor a 0')]
    private ?int $clienteId = null;

    #[Assert\NotBlank(message: 'El monto es requerido')]
    #[Assert\GreaterThan(value: 0, message: 'El monto debe ser mayor a 0')]
    #[Assert\LessThanOrEqual(value: 99999.99, message: 'El monto no puede exceder 99999.99')]
    private float $monto = 0;

    #[Assert\NotBlank(message: 'La descripción es requerida')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'La descripción debe tener al menos 5 caracteres',
        maxMessage: 'La descripción no debe exceder 255 caracteres'
    )]
    private string $descripcion = '';

    public function getClienteId(): ?int
    {
        return $this->clienteId;
    }

    public function setClienteId(?int $clienteId): self
    {
        $this->clienteId = $clienteId;
        return $this;
    }

    public function getMonto(): float
    {
        return $this->monto;
    }

    public function setMonto(float $monto): self
    {
        $this->monto = $monto;
        return $this;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }
}
