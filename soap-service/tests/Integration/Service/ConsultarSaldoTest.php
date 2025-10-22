<?php

namespace App\Tests\Integration\Service;

use App\Entity\Cliente;
use App\Entity\Billetera;
use App\Service\WalletService;
use App\Constants\ErrorCodes;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ConsultarSaldoTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private WalletService $walletService;
    private int $clienteId;
    private string $documento = '1234567890';
    private string $celular = '3001234567';

    protected function setUp(): void
    {
        // Inicializa el kernel
        self::bootKernel();
        
        // Obtener servicios del contenedor
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->walletService = self::getContainer()->get(WalletService::class);
        
        // Crear cliente de prueba
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento($this->documento);
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Perez');
        $cliente->setEmail('juan@test.com');
        $cliente->setCelular($this->celular);
        $cliente->setFechaRegistro(new \DateTime());
        
        $this->entityManager->persist($cliente);
        $this->entityManager->flush();
        
        $this->clienteId = $cliente->getId();
        
        // Crear billetera con saldo inicial
        $billetera = new Billetera();
        $billetera->setCliente($cliente);
        $billetera->setSaldo(100000);
        $billetera->setFechaCreacion(new \DateTime());
        
        $this->entityManager->persist($billetera);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        // Limpiar datos de prueba
        $this->entityManager->createQuery('DELETE FROM App\Entity\Transaccion t WHERE t.billetera IN (SELECT b.id FROM App\Entity\Billetera b WHERE b.cliente = :clienteId)')
            ->setParameter('clienteId', $this->clienteId)
            ->execute();
            
        $this->entityManager->createQuery('DELETE FROM App\Entity\Billetera b WHERE b.cliente = :clienteId')
            ->setParameter('clienteId', $this->clienteId)
            ->execute();
            
        $this->entityManager->createQuery('DELETE FROM App\Entity\Cliente c WHERE c.id = :clienteId')
            ->setParameter('clienteId', $this->clienteId)
            ->execute();
            
        parent::tearDown();
    }

    public function testConsultarSaldoExitoso(): void
    {
        $result = $this->walletService->consultarSaldo(
            $this->clienteId,
            $this->documento,
            $this->celular
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('00', $result['cod_error']);
        $this->assertEquals('Consulta realizada exitosamente', $result['message_error']);
        
        $data = $result['data'];
        $this->assertEquals(100000, $data['saldo']);
        $this->assertEquals(0, $data['totalTransacciones']);
        $this->assertEquals($this->clienteId, $data['cliente']['id']);
        $this->assertEquals('Juan', $data['cliente']['nombres']);
        $this->assertEquals('Perez', $data['cliente']['apellidos']);
    }

    public function testClienteNoEncontrado(): void
    {
        $result = $this->walletService->consultarSaldo(
            99999,
            '1234567890',
            '3001234567'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('03', $result['cod_error']);
        $this->assertEquals('Cliente no encontrado', $result['message_error']);
    }

    public function testDatosIncorrectosDocumentoCelularNoCoinciden(): void
    {
        $result = $this->walletService->consultarSaldo(
            $this->clienteId,
            '1111111111', // Documento incorrecto
            $this->celular
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('04', $result['cod_error']);
        $this->assertEquals('Los datos de documento y celular no coinciden con el cliente', $result['message_error']);
    }

    public function testValidacionCamposRequeridos(): void
    {
        $result = $this->walletService->consultarSaldo(
            0, // clienteId inválido
            '', // documento vacío
            '123' // celular inválido
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('01', $result['cod_error']);
        $this->assertStringContainsString('Campos requeridos inválidos', $result['message_error']);
    }
}