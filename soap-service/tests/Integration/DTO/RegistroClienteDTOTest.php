<?php

namespace App\Tests\Integration\DTO;

use App\DTOs\RegistroClienteDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RegistroClienteDTOTest extends KernelTestCase
{
    private $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    public function testTipoDocumentoNotBlank(): void
    {
        $dto = new RegistroClienteDTO();
        $dto->setTipoDocumento('');
        $dto->setNumeroDocumento('1234567890');
        $dto->setNombres('Juan');
        $dto->setApellidos('Pérez');
        $dto->setEmail('juan@example.com');
        $dto->setCelular('3001234567');

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($violations));
    }

    public function testEmailFormatValid(): void
    {
        $dto = new RegistroClienteDTO();
        $dto->setTipoDocumento('CC');
        $dto->setNumeroDocumento('1234567890');
        $dto->setNombres('Juan');
        $dto->setApellidos('Pérez');
        $dto->setEmail('invalid_email');
        $dto->setCelular('3001234567');

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($violations));
    }

    public function testCelularFormatValid(): void
    {
        $dto = new RegistroClienteDTO();
        $dto->setTipoDocumento('CC');
        $dto->setNumeroDocumento('1234567890');
        $dto->setNombres('Juan');
        $dto->setApellidos('Pérez');
        $dto->setEmail('juan@example.com');
        $dto->setCelular('300');

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($violations));
    }

    public function testValidDTONoViolations(): void
    {
        $dto = new RegistroClienteDTO();
        $dto->setTipoDocumento('CC');
        $dto->setNumeroDocumento('1234567890');
        $dto->setNombres('Juan');
        $dto->setApellidos('Pérez');
        $dto->setEmail('juan@example.com');
        $dto->setCelular('3001234567');

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }
}
