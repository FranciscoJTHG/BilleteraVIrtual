<?php

namespace App\Tests\Integration\Service;

use App\Entity\Cliente;
use App\Entity\Billetera;
use App\Entity\PagoPendiente;
use App\Service\WalletService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class PagarTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private WalletService $walletService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->walletService = self::getContainer()->get(WalletService::class);

        $this->limpiarBD();
    }

    protected function tearDown(): void
    {
        $this->limpiarBD();
        parent::tearDown();
    }

    private function limpiarBD(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM transacciones');
        $connection->executeStatement('DELETE FROM pagos_pendientes');
        $connection->executeStatement('DELETE FROM billeteras');
        $connection->executeStatement('DELETE FROM clientes');
    }

    public function testPagarExitoso(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];

        $this->walletService->recargaBilletera(
            $clienteId,
            '1234567890',
            '3001234567',
            100.00,
            'RECARGA-TEST'
        );

        $resultado = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 50.00,
            descripcion: 'Compra en tienda'
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals('00', $resultado['cod_error']);
        $this->assertArrayHasKey('data', $resultado);
        $this->assertArrayHasKey('sessionId', $resultado['data']);
        $this->assertEquals(50.00, $resultado['data']['monto']);
        $this->assertEquals('15 minutos', $resultado['data']['tiempoExpiracion']);
    }

    public function testPagarCreaRegistroPendiente(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $clienteId = $response['data']['id'];

        $this->walletService->recargaBilletera(
            $clienteId,
            '1234567890',
            '3001234567',
            100.00,
            'RECARGA-TEST'
        );

        $resultado = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 25.50,
            descripcion: 'Pago de servicios'
        );

        $this->assertTrue($resultado['success']);
        $sessionId = $resultado['data']['sessionId'];

        $this->entityManager->clear();
        $pagoPendiente = $this->entityManager
            ->getRepository(PagoPendiente::class)
            ->findBySessionId($sessionId);

        $this->assertNotNull($pagoPendiente, 'El pago pendiente debe existir en BD');
        $this->assertEquals('25.50', $pagoPendiente->getMonto());
        $this->assertEquals('Pago de servicios', $pagoPendiente->getDescripcion());
        $this->assertEquals('pendiente', $pagoPendiente->getEstado());
        $this->assertNotNull($pagoPendiente->getToken());
    }

    public function testPagarSaldoInsuficiente(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $clienteId = $response['data']['id'];

        $this->walletService->recargaBilletera(
            $clienteId,
            '1234567890',
            '3001234567',
            100.00,
            'RECARGA-TEST-001'
        );

        $resultado = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 50.00,
            descripcion: 'Compra de producto'
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals('00', $resultado['cod_error']);
    }

    public function testPagarClienteNoEncontrado(): void
    {
        $resultado = $this->walletService->pagar(
            clienteId: 9999,
            monto: 50.00,
            descripcion: 'Compra de producto'
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('03', $resultado['cod_error']);
        $this->assertStringContainsString('Cliente no encontrado', $resultado['message_error']);
    }

    public function testPagarMontoInvalido(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $clienteId = $response['data']['id'];

        $resultado = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: -10.00,
            descripcion: 'Compra de producto'
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('01', $resultado['cod_error']);
        $this->assertStringContainsString('monto', strtolower($resultado['message_error']));
    }

    public function testPagarDescripcionInvalida(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $clienteId = $response['data']['id'];

        $this->walletService->recargaBilletera(
            $clienteId,
            '1234567890',
            '3001234567',
            100.00,
            'RECARGA-TEST'
        );

        $resultado = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 50.00,
            descripcion: 'abc'
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('01', $resultado['cod_error']);
        $this->assertStringContainsString('descripción', strtolower($resultado['message_error']));
    }
}
