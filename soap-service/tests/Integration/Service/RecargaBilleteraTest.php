<?php

namespace App\Tests\Integration\Service;

use App\Entity\Cliente;
use App\Entity\Billetera;
use App\Entity\Transaccion;
use App\Service\WalletService;
use App\Constants\ErrorCodes;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class RecargaBilleteraTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private WalletService $walletService;
    private int $clienteId;
    private string $documento = '9876543210';
    private string $celular = '3009876543';

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->walletService = self::getContainer()->get(WalletService::class);
        
        // Limpiar completamente la base de datos antes de crear datos
        $this->limpiarBD();
        
        // Crear cliente de prueba
        $cliente = new Cliente();
        $cliente->setTipoDocumento('CC');
        $cliente->setNumeroDocumento($this->documento);
        $cliente->setNombres('Juan');
        $cliente->setApellidos('Perez');
        $cliente->setEmail('juan.recarga@test.com');
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

    private function limpiarBD(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM transacciones');
        $connection->executeStatement('DELETE FROM pagos_pendientes');
        $connection->executeStatement('DELETE FROM billeteras');
        $connection->executeStatement('DELETE FROM clientes');
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

    public function testRecargaBilleteraExitosa(): void
    {
        $result = $this->walletService->recargaBilletera(
            $this->clienteId,
            $this->documento,
            $this->celular,
            50000,
            'RECARGA-001'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('00', $result['cod_error']);
        $this->assertEquals('Recarga realizada exitosamente', $result['message_error']);
        
        $data = $result['data'];
        $this->assertEquals(150000, $data['nuevoSaldo']); // 100000 + 50000
        $this->assertEquals(50000, $data['monto']);
        $this->assertEquals('RECARGA-001', $data['referencia']);
        $this->assertArrayHasKey('transaccionId', $data);
        $this->assertArrayHasKey('fecha', $data);
    }

    public function testClienteNoEncontrado(): void
    {
        $result = $this->walletService->recargaBilletera(
            99999,
            '1234567890',
            '3001234567',
            50000,
            'RECARGA-002'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('03', $result['cod_error']);
        $this->assertEquals('Cliente no encontrado', $result['message_error']);
    }

    public function testDatosIncorrectosDocumentoCelularNoCoinciden(): void
    {
        $result = $this->walletService->recargaBilletera(
            $this->clienteId,
            '1111111111', // Documento incorrecto
            $this->celular,
            50000,
            'RECARGA-003'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('04', $result['cod_error']);
        $this->assertEquals('Los datos de documento y celular no coinciden con el cliente', $result['message_error']);
    }

    public function testValidacionCamposRequeridos(): void
    {
        $result = $this->walletService->recargaBilletera(
            0, // clienteId inválido
            '', // documento vacío
            '123', // celular inválido
            -100, // monto inválido
            '' // referencia vacía
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('01', $result['cod_error']);
        $this->assertStringContainsString('Campos requeridos inválidos', $result['message_error']);
    }
}