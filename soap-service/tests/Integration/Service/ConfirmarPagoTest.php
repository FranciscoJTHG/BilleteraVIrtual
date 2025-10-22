<?php

namespace App\Tests\Integration\Service;

use App\Entity\Cliente;
use App\Entity\Billetera;
use App\Entity\PagoPendiente;
use App\Entity\Transaccion;
use App\Service\WalletService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ConfirmarPagoTest extends KernelTestCase
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

    public function testConfirmarPagoExitoso(): void
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

        $pagoResponse = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 50.00,
            descripcion: 'Compra en tienda'
        );
        $this->assertTrue($pagoResponse['success']);
        $sessionId = $pagoResponse['data']['sessionId'];

        $this->entityManager->clear();
        $pagoPendiente = $this->entityManager
            ->getRepository(PagoPendiente::class)
            ->findBySessionId($sessionId);
        $token = $pagoPendiente->getToken();

        $resultado = $this->walletService->confirmarPago(
            sessionId: $sessionId,
            token: $token
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals('00', $resultado['cod_error']);
        $this->assertArrayHasKey('data', $resultado);
        $this->assertArrayHasKey('transaccionId', $resultado['data']);
        $this->assertEquals('50.00', $resultado['data']['monto']);
    }

    public function testConfirmarPagoCreaTransaccion(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        $this->walletService->recargaBilletera(
            $clienteId,
            '1234567890',
            '3001234567',
            100.00,
            'RECARGA-TEST'
        );

        $pagoResponse = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 40.00,
            descripcion: 'Pago de servicios'
        );
        $sessionId = $pagoResponse['data']['sessionId'];

        $this->entityManager->clear();
        $pagoPendiente = $this->entityManager
            ->getRepository(PagoPendiente::class)
            ->findBySessionId($sessionId);
        $token = $pagoPendiente->getToken();

        $resultado = $this->walletService->confirmarPago(
            sessionId: $sessionId,
            token: $token
        );

        $this->assertTrue($resultado['success']);
        $transaccionId = $resultado['data']['transaccionId'];

        $this->entityManager->clear();
        $transaccion = $this->entityManager
            ->getRepository(Transaccion::class)
            ->find($transaccionId);

        $this->assertNotNull($transaccion, 'La transacción debe existir en BD');
        $this->assertEquals('pago', $transaccion->getTipo());
        $this->assertEquals('40.00', $transaccion->getMonto());
        $this->assertEquals('completada', $transaccion->getEstado());
        $this->assertEquals($billeteraId, $transaccion->getBilletera()->getId());
    }

    public function testConfirmarPagoActualizaSaldo(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        $this->walletService->recargaBilletera(
            $clienteId,
            '1234567890',
            '3001234567',
            100.00,
            'RECARGA-TEST'
        );

        $pagoResponse = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 35.75,
            descripcion: 'Compra de producto'
        );
        $sessionId = $pagoResponse['data']['sessionId'];

        $this->entityManager->clear();
        $pagoPendiente = $this->entityManager
            ->getRepository(PagoPendiente::class)
            ->findBySessionId($sessionId);
        $token = $pagoPendiente->getToken();

        $resultado = $this->walletService->confirmarPago(
            sessionId: $sessionId,
            token: $token
        );

        $this->assertTrue($resultado['success']);
        $saldoNuevo = $resultado['data']['nuevoSaldo'];

        $this->entityManager->clear();
        $billetera = $this->entityManager
            ->getRepository(Billetera::class)
            ->find($billeteraId);

        $saldoEsperado = number_format(100.00 - 35.75, 2, '.', '');
        $this->assertEquals($saldoEsperado, $billetera->getSaldo(),
            'El saldo debe decrementarse correctamente');
        $this->assertEquals($saldoEsperado, $saldoNuevo);
    }

    public function testConfirmarPagoTokenIncorrecto(): void
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

        $pagoResponse = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 50.00,
            descripcion: 'Compra en tienda'
        );
        $sessionId = $pagoResponse['data']['sessionId'];

        $resultado = $this->walletService->confirmarPago(
            sessionId: $sessionId,
            token: '000000'
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('07', $resultado['cod_error']);
        $this->assertStringContainsString('Token incorrecto', $resultado['message_error']);
    }

    public function testConfirmarPagoSesionExpirada(): void
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

        $pagoResponse = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 50.00,
            descripcion: 'Compra en tienda'
        );
        $sessionId = $pagoResponse['data']['sessionId'];

        $this->entityManager->clear();
        $pagoPendiente = $this->entityManager
            ->getRepository(PagoPendiente::class)
            ->findBySessionId($sessionId);

        $pagoPendiente->setFechaExpiracion((new \DateTime())->modify('-1 minute'));
        $this->entityManager->persist($pagoPendiente);
        $this->entityManager->flush();

        $resultado = $this->walletService->confirmarPago(
            sessionId: $sessionId,
            token: $pagoPendiente->getToken()
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('08', $resultado['cod_error']);
        $this->assertStringContainsString('Sesión expirada', $resultado['message_error']);
    }

    public function testConfirmarPagoSesionNoEncontrada(): void
    {
        $resultado = $this->walletService->confirmarPago(
            sessionId: '550e8400-e29b-41d4-a716-446655440000',
            token: '123456'
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('06', $resultado['cod_error']);
        $this->assertStringContainsString('Sesión de pago no encontrada', $resultado['message_error']);
    }
}
