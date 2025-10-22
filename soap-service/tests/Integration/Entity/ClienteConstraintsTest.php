<?php

namespace App\Tests\Integration\Entity;

use App\Entity\Cliente;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClienteConstraintsTest extends KernelTestCase
{
    private $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    /**
     * TEST 2.1: Validar que tipoDocumento no vacío
     */
    public function testTipoDocumentoNotBlank(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('tipo de documento', $violations[0]->getMessage());
    }

    /**
     * TEST 2.2: Validar que numeroDocumento no vacío
     */
    public function testNumeroDocumentoNotBlank(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('número de documento', $violations[0]->getMessage());
    }

    /**
     * TEST 2.3: Validar que nombres no vacío
     */
    public function testNombresNotBlank(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('nombre', $violations[0]->getMessage());
    }

    /**
     * TEST 2.4: Validar que apellidos no vacío
     */
    public function testApellidosNotBlank(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('apellido', $violations[0]->getMessage());
    }

    /**
     * TEST 2.5: Validar que email no vacío
     */
    public function testEmailNotBlank(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('correo', $violations[0]->getMessage());
    }

    /**
     * TEST 2.6: Validar que email tenga formato válido
     */
    public function testEmailFormatValid(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('email_invalido');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('válido', $violations[0]->getMessage());
    }

    /**
     * TEST 2.7: Validar que celular no vacío
     */
    public function testCelularNotBlank(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('celular', $violations[0]->getMessage());
    }

    /**
     * TEST 2.8: Validar que celular tenga exactamente 10 dígitos
     */
    public function testCelularFormatValid(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('300123');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('10 dígitos', $violations[0]->getMessage());
    }

    /**
     * TEST 2.9: Validar que cliente válido no tiene violaciones
     */
    public function testClienteValidoSinViolaciones(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertCount(0, $violations, 'Un cliente válido no debe tener violaciones');
    }

    /**
     * TEST 2.10: Validar que tipoDocumento tenga longitud válida (2-10)
     */
    public function testTipoDocumentoLengthValid(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('A');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('2 caracteres', $violations[0]->getMessage());
    }

    /**
     * TEST 2.11: Validar que numeroDocumento tenga longitud válida (5-20)
     */
    public function testNumeroDocumentoLengthValid(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('123');
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('5 caracteres', $violations[0]->getMessage());
    }

    /**
     * TEST 2.12: Validar que nombres tenga longitud válida (2-255)
     */
    public function testNombresLengthValid(): void
    {
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento('1234567890');
        $cliente->setNombres('J');
        $cliente->setApellidos('Pérez');
        $cliente->setEmail('juan@example.com');
        $cliente->setCelular('3001234567');
        $cliente->setFechaRegistro(new \DateTime());

        $violations = $this->validator->validate($cliente);

        $this->assertGreaterThan(0, count($violations));
        $this->assertStringContainsString('2 caracteres', $violations[0]->getMessage());
    }
}
