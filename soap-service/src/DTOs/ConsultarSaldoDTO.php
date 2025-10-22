<?php

namespace App\DTOs;

use Symfony\Component\Validator\Constraints as Assert;

class ConsultarSaldoDTO
{
    #[Assert\NotBlank(message: 'El clienteId es requerido')]
    #[Assert\Type(type: 'integer', message: 'El clienteId debe ser un número entero')]
    #[Assert\Positive(message: 'El clienteId debe ser un número positivo')]
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
}