<?php

namespace App\DTOs;

use Symfony\Component\Validator\Constraints as Assert;

class ConfirmarPagoDTO
{
    #[Assert\NotBlank(message: 'El sessionId es requerido')]
    #[Assert\Uuid(message: 'El sessionId debe ser un UUID válido')]
    private string $sessionId = '';

    #[Assert\NotBlank(message: 'El token es requerido')]
    #[Assert\Regex(
        pattern: '/^\d{6}$/',
        message: 'El token debe ser un número de 6 dígitos'
    )]
    private string $token = '';

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }
}
