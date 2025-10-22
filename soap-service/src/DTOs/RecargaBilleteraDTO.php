<?php

namespace App\DTOs;

use Symfony\Component\Validator\Constraints as Assert;

class RecargaBilleteraDTO
{
    #[Assert\NotBlank(message: 'El cliente ID es requerido')]
    #[Assert\GreaterThan(value: 0, message: 'El cliente ID debe ser mayor a 0')]
    private ?int $clienteId = null;

    #[Assert\NotBlank(message: 'El tipo de documento es requerido')]
    #[Assert\Length(min: 2, max: 10, minMessage: 'El tipo de documento debe tener al menos 2 caracteres', maxMessage: 'El tipo de documento no debe exceder 10 caracteres')]
    private string $documento = '';

    #[Assert\NotBlank(message: 'El número de celular es requerido')]
    #[Assert\Regex(pattern: '/^\d{10}$/', message: 'El número de celular debe contener exactamente 10 dígitos')]
    private string $numeroCelular = '';

    #[Assert\NotBlank(message: 'El valor es requerido')]
    #[Assert\GreaterThan(value: 0, message: 'El valor debe ser mayor a 0')]
    private float $valor = 0;

    public function getClienteId(): ?int
    {
        return $this->clienteId;
    }

    public function setClienteId(?int $clienteId): self
    {
        $this->clienteId = $clienteId;
        return $this;
    }

    public function getDocumento(): string
    {
        return $this->documento;
    }

    public function setDocumento(string $documento): self
    {
        $this->documento = $documento;
        return $this;
    }

    public function getNumeroCelular(): string
    {
        return $this->numeroCelular;
    }

    public function setNumeroCelular(string $numeroCelular): self
    {
        $this->numeroCelular = $numeroCelular;
        return $this;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function setValor(float $valor): self
    {
        $this->valor = $valor;
        return $this;
    }
}
