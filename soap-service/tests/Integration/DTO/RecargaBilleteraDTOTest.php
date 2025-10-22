<?php

namespace App\Tests\Integration\DTO;

use App\DTOs\RecargaBilleteraDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RecargaBilleteraDTOTest extends KernelTestCase
{
    private $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    public function testMontoNotBlank(): void
    {
        $dto = new RecargaBilleteraDTO();
        $dto->setTipoDocumento('CC');
        $dto->setNumeroDocumento('1234567890');
        $dto->setCelular('3001234567');

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($violations));
    }

    public function testMontoGreaterThanZero(): void
    {
        $dto = new RecargaBilleteraDTO();
        $dto->setTipoDocumento('CC');
        $dto->setNumeroDocumento('1234567890');
        $dto->setCelular('3001234567');
        $dto->setMonto(0);

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($violations));
    }

    public function testCelularFormatValid(): void
    {
        $dto = new RecargaBilleteraDTO();
        $dto->setTipoDocumento('CC');
        $dto->setNumeroDocumento('1234567890');
        $dto->setCelular('300');
        $dto->setMonto(50.00);

        $violations = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($violations));
    }

    public function testValidDTONoViolations(): void
    {
        $dto = new RecargaBilleteraDTO();
        $dto->setTipoDocumento('CC');
        $dto->setNumeroDocumento('1234567890');
        $dto->setCelular('3001234567');
        $dto->setMonto(50.00);

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }
}
