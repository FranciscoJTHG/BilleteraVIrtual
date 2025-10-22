<?php

namespace App\DTOs;

use Symfony\Component\Validator\Constraints as Assert;

class RegistroClienteDTO
{
    #[Assert\NotBlank(message: 'El tipo de documento es requerido')]
    #[Assert\Length(min: 2, max: 10, minMessage: 'El tipo de documento debe tener al menos 2 caracteres', maxMessage: 'El tipo de documento no debe exceder 10 caracteres')]
    private string $tipoDocumento = '';

    #[Assert\NotBlank(message: 'El número de documento es requerido')]
    #[Assert\Length(min: 5, max: 20, minMessage: 'El número de documento debe tener al menos 5 caracteres', maxMessage: 'El número de documento no debe exceder 20 caracteres')]
    private string $numeroDocumento = '';

    #[Assert\NotBlank(message: 'El nombre es requerido')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'El nombre debe tener al menos 2 caracteres', maxMessage: 'El nombre no debe exceder 255 caracteres')]
    private string $nombres = '';

    #[Assert\NotBlank(message: 'El apellido es requerido')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'El apellido debe tener al menos 2 caracteres', maxMessage: 'El apellido no debe exceder 255 caracteres')]
    private string $apellidos = '';

    #[Assert\NotBlank(message: 'El correo electrónico es requerido')]
    #[Assert\Email(mode: 'html5', message: 'El correo electrónico no es válido')]
    private string $email = '';

    #[Assert\NotBlank(message: 'El celular es requerido')]
    #[Assert\Regex(pattern: '/^\d{10}$/', message: 'El celular debe contener exactamente 10 dígitos')]
    private string $celular = '';

    public function getTipoDocumento(): string
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(string $tipoDocumento): self
    {
        $this->tipoDocumento = $tipoDocumento;
        return $this;
    }

    public function getNumeroDocumento(): string
    {
        return $this->numeroDocumento;
    }

    public function setNumeroDocumento(string $numeroDocumento): self
    {
        $this->numeroDocumento = $numeroDocumento;
        return $this;
    }

    public function getNombres(): string
    {
        return $this->nombres;
    }

    public function setNombres(string $nombres): self
    {
        $this->nombres = $nombres;
        return $this;
    }

    public function getApellidos(): string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): self
    {
        $this->apellidos = $apellidos;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getCelular(): string
    {
        return $this->celular;
    }

    public function setCelular(string $celular): self
    {
        $this->celular = $celular;
        return $this;
    }
}
