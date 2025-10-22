<?php

namespace App\DTOs;

use Symfony\Component\Validator\Constraints as Assert;

class RecargaBilleteraDTO
{
    #[Assert\NotBlank(message: 'El cliente ID es requerido')]
    #[Assert\GreaterThan(value: 0, message: 'El cliente ID debe ser mayor a 0')]
    private ?int $clienteId = null;

    #[Assert\NotBlank(message: 'El documento es requerido')]
    #[Assert\Type(type: 'string', message: 'El documento debe ser una cadena de texto')]
    #[Assert\Length(min: 5, max: 20, minMessage: 'El documento debe tener al menos 5 caracteres', maxMessage: 'El documento no puede exceder 20 caracteres')]
    private ?string $documento = null;

    #[Assert\NotBlank(message: 'El celular es requerido')]
    #[Assert\Type(type: 'string', message: 'El celular debe ser una cadena de texto')]
    #[Assert\Length(exactly: 10, exactMessage: 'El celular debe tener exactamente 10 dígitos')]
    #[Assert\Regex(pattern: '/^[0-9]{10}$/', message: 'El celular debe contener solo números y tener 10 dígitos')]
    private ?string $celular = null;

    #[Assert\NotBlank(message: 'El monto es requerido')]
    #[Assert\Type(type: 'numeric', message: 'El monto debe ser un número')]
    #[Assert\GreaterThan(value: 0, message: 'El monto debe ser mayor a 0')]
    private float $monto = 0;

    #[Assert\NotBlank(message: 'La referencia es requerida')]
    #[Assert\Type(type: 'string', message: 'La referencia debe ser una cadena de texto')]
    #[Assert\Length(min: 1, max: 50, minMessage: 'La referencia debe tener al menos 1 caracter', maxMessage: 'La referencia no puede exceder 50 caracteres')]
    private string $referencia = '';

    public function getClienteId(): ?int
    {
        return $this->clienteId;
    }

    public function setClienteId(?int $clienteId): self
    {
        $this->clienteId = $clienteId;
        return $this;
    }

    public function getDocumento(): ?string
    {
        return $this->documento;
    }

    public function setDocumento(?string $documento): self
    {
        $this->documento = $documento;
        return $this;
    }

    public function getCelular(): ?string
    {
        return $this->celular;
    }

    public function setCelular(?string $celular): self
    {
        $this->celular = $celular;
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

    public function getReferencia(): string
    {
        return $this->referencia;
    }

    public function setReferencia(string $referencia): self
    {
        $this->referencia = $referencia;
        return $this;
    }
}
