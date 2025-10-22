<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClienteRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClienteRepository::class)]
#[ORM\Table(name: 'clientes', indexes: [
    new ORM\Index(name: 'idx_documento', columns: ['numero_documento']),
    new ORM\Index(name: 'idx_email', columns: ['email'])
])]
class Cliente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'El tipo de documento es requerido')]
    #[Assert\Length(min: 2, max: 10, minMessage: 'El tipo de documento debe tener al menos 2 caracteres', maxMessage: 'El tipo de documento no debe exceder 10 caracteres')]
    #[ORM\Column(type: 'string', length: 10)]
    private string $tipoDocumento;

    #[Assert\NotBlank(message: 'El número de documento es requerido')]
    #[Assert\Length(min: 5, max: 20, minMessage: 'El número de documento debe tener al menos 5 caracteres', maxMessage: 'El número de documento no debe exceder 20 caracteres')]
    #[ORM\Column(type: 'string', length: 20, unique: true)]
    private string $numeroDocumento;

    #[Assert\NotBlank(message: 'El nombre es requerido')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'El nombre debe tener al menos 2 caracteres', maxMessage: 'El nombre no debe exceder 255 caracteres')]
    #[ORM\Column(type: 'string', length: 255)]
    private string $nombres;

    #[Assert\NotBlank(message: 'El apellido es requerido')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'El apellido debe tener al menos 2 caracteres', maxMessage: 'El apellido no debe exceder 255 caracteres')]
    #[ORM\Column(type: 'string', length: 255)]
    private string $apellidos;

    #[Assert\NotBlank(message: 'El correo electrónico es requerido')]
    #[Assert\Email(mode: 'html5', message: 'El correo electrónico no es válido')]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[Assert\NotBlank(message: 'El celular es requerido')]
    #[Assert\Regex(pattern: '/^\d{10}$/', message: 'El celular debe contener exactamente 10 dígitos')]
    #[ORM\Column(type: 'string', length: 20)]
    private string $celular;

    #[ORM\OneToOne(targetEntity: Billetera::class, mappedBy: 'cliente', cascade: ['persist', 'remove'])]
    private ?Billetera $billetera = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fechaRegistro;

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

    public function getTipoDocumento(): string
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(string $tipoDocumento): static
    {
        $this->tipoDocumento = $tipoDocumento;
        return $this;
    }

    public function getNumeroDocumento(): string
    {
        return $this->numeroDocumento;
    }

    public function setNumeroDocumento(string $numeroDocumento): static
    {
        $this->numeroDocumento = $numeroDocumento;
        return $this;
    }

    public function getNombres(): string
    {
        return $this->nombres;
    }

    public function setNombres(string $nombres): static
    {
        $this->nombres = $nombres;
        return $this;
    }

    public function getApellidos(): string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getCelular(): string
    {
        return $this->celular;
    }

    public function setCelular(string $celular): static
    {
        $this->celular = $celular;
        return $this;
    }

    public function getBilletera(): ?Billetera
    {
        return $this->billetera;
    }

    public function setBilletera(?Billetera $billetera): static
    {
        if ($billetera === null && $this->billetera !== null) {
            $this->billetera->setCliente(null);
        }

        if ($billetera !== null && $billetera->getCliente() !== $this) {
            $billetera->setCliente($this);
        }

        $this->billetera = $billetera;
        return $this;
    }

    public function getFechaRegistro(): \DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): static
    {
        $this->fechaRegistro = $fechaRegistro;
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
}
