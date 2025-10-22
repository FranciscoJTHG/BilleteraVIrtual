<?php

namespace App\Tests\Integration\Service;

use App\Entity\Cliente;
use App\Entity\Billetera;
use App\Entity\Transaccion;
use App\Service\WalletService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class RecargaBilleteraTest extends KernelTestCase
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

    public function testRecargaBilleteraExitosa(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];

        $resultado = $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            documento: 'CC',
            numeroCelular: '3001234567',
            valor: 50.00
        );

         $this->assertTrue($resultado['success']);
         $this->assertEquals('00', $resultado['cod_error']);
         $this->assertArrayHasKey('data', $resultado);
         $this->assertEquals('50.00', $resultado['data']['nuevoSaldo']);
         $this->assertEquals('50.00', $resultado['data']['valor']);
    }

    public function testRecargaBilleteraActualizaSaldo(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $saldoInicial = $response['data']['billetera']['saldo'];
        $billeteraId = $response['data']['billetera']['id'];

        $resultado = $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            documento: 'CC',
            numeroCelular: '3001234567',
            valor: 75.50
        );

        $this->assertTrue($resultado['success']);
        
        $this->entityManager->clear();

        $billeteraRecargada = $this->entityManager
            ->getRepository(Billetera::class)
            ->find($billeteraId);

        $saldoEsperado = number_format((float)$saldoInicial + 75.50, 2, '.', '');
        $this->assertEquals($saldoEsperado, $billeteraRecargada->getSaldo(),
            'El saldo debe incrementarse correctamente');
    }

    public function testRecargaBilleteraCreaTransaccion(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        $resultado = $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            documento: 'CC',
            numeroCelular: '3001234567',
            valor: 100.00
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals('00', $resultado['cod_error']);
        $transaccionId = $resultado['data']['transaccionId'];

        $this->entityManager->clear();
        $transaccionRecuperada = $this->entityManager
            ->getRepository(Transaccion::class)
            ->find($transaccionId);

        $this->assertNotNull($transaccionRecuperada, 'La transacción debe existir en BD');
        $this->assertEquals('recarga', $transaccionRecuperada->getTipo());
        $this->assertEquals('100.00', $transaccionRecuperada->getMonto());
        $this->assertEquals($billeteraId, $transaccionRecuperada->getBilletera()->getId());
    }

    public function testRecargaBilleteraPersistenteEnBD(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        $resultado = $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            documento: 'CC',
            numeroCelular: '3001234567',
            valor: 25.75
        );

        $this->assertTrue($resultado['success']);
        $transaccionId = $resultado['data']['transaccionId'];

        $this->entityManager->clear();

        $billeteraRecuperada = $this->entityManager
            ->getRepository(Billetera::class)
            ->find($billeteraId);

        $this->assertEquals('25.75', $billeteraRecuperada->getSaldo(),
            'El saldo debe persistir en BD');

        $transaccionRecuperada = $this->entityManager
            ->getRepository(Transaccion::class)
            ->find($transaccionId);

        $this->assertNotNull($transaccionRecuperada, 'La transacción debe persistir en BD');
        $this->assertEquals('recarga', $transaccionRecuperada->getTipo());
        $this->assertEquals('25.75', $transaccionRecuperada->getMonto());
    }

    public function testRecargaBilleteraClienteNoEncontrado(): void
    {
        $resultado = $this->walletService->recargaBilletera(
            clienteId: 9999,
            documento: 'CC',
            numeroCelular: '9999999999',
            valor: 50.00
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('03', $resultado['cod_error']);
        $this->assertStringContainsString('Cliente no encontrado', $resultado['message_error']);
        $this->assertEmpty($resultado['data']);
    }

    public function testRecargaMultiplesVeces(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            documento: 'CC',
            numeroCelular: '3001234567',
            valor: 50.00
        );

        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            documento: 'CC',
            numeroCelular: '3001234567',
            valor: 25.50
        );

        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            documento: 'CC',
            numeroCelular: '3001234567',
            valor: 12.25
        );

        $this->entityManager->clear();

        $billeteraFinal = $this->entityManager
            ->getRepository(Billetera::class)
            ->find($billeteraId);

        $saldoEsperado = number_format(50.00 + 25.50 + 12.25, 2, '.', '');
        $this->assertEquals($saldoEsperado, $billeteraFinal->getSaldo(),
            'El saldo debe acumular todas las recargas');

        $transacciones = $this->entityManager
            ->getRepository(Transaccion::class)
            ->findBy(['billetera' => $billeteraId]);

        $this->assertCount(3, $transacciones, 'Deben existir 3 transacciones');

        $montos = array_map(fn($t) => $t->getMonto(), $transacciones);
        $this->assertContains('50.00', $montos);
        $this->assertContains('25.50', $montos);
        $this->assertContains('12.25', $montos);
    }
}
