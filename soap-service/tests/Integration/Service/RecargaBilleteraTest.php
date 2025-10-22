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
        // Inicializa el kernel
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->walletService = self::getContainer()->get(WalletService::class);

        // Limpia la BD antes de cada test
        $this->limpiarBD();
    }

    protected function tearDown(): void
    {
        // Limpia la BD después de cada test
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

    /**
     * TEST 2.1: Happy Path - Recarga Exitosa
     * Verificar que una recarga simple funciona correctamente
     */
    public function testRecargaBilleteraExitosa(): void
    {
        // Crear cliente primero
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];

        // Ejecutar recarga
        $transaccion = $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 50.00,
            referencia: 'RECARGA-001'
        );

        // Validar que retorna una transacción
        $this->assertInstanceOf(Transaccion::class, $transaccion);
        $this->assertNotNull($transaccion->getId(), 'La transacción debe tener un ID generado');

        // Validar datos de la transacción
        $this->assertEquals('recarga', $transaccion->getTipo());
        $this->assertEquals('50.00', $transaccion->getMonto());
        $this->assertEquals('RECARGA-001', $transaccion->getReferencia());
        $this->assertEquals('completada', $transaccion->getEstado());

        // Validar fecha reciente
        $fecha = $transaccion->getFecha();
        $this->assertInstanceOf(\DateTimeInterface::class, $fecha);
        $ahora = new \DateTime();
        $diferencia = abs($ahora->getTimestamp() - $fecha->getTimestamp());
        $this->assertLessThanOrEqual(1, $diferencia, 'La fecha debe ser reciente');
    }

    /**
     * TEST 2.2: Actualización de Saldo
     * Verificar que el saldo de la billetera se incrementa correctamente
     */
    public function testRecargaBilleteraActualizaSaldo(): void
    {
        // Crear cliente
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $saldoInicial = $response['data']['billetera']['saldo'];
        $billeteraId = $response['data']['billetera']['id'];

        // Ejecutar recarga
        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 75.50,
            referencia: 'RECARGA-002'
        );

        // Limpiar cache para forzar fresh query
        $this->entityManager->clear();

        // Recargar billetera desde BD
        $billeteraRecargada = $this->entityManager
            ->getRepository(Billetera::class)
            ->find($billeteraId);

        // Verificar que el saldo se incrementó correctamente
        $saldoEsperado = number_format((float)$saldoInicial + 75.50, 2, '.', '');
        $this->assertEquals($saldoEsperado, $billeteraRecargada->getSaldo(),
            'El saldo debe incrementarse correctamente');
    }

    /**
     * TEST 2.3: Creación de Transacción
     * Verificar que se crea un registro en la tabla transacciones
     */
    public function testRecargaBilleteraCreaTransaccion(): void
    {
        // Crear cliente
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        // Ejecutar recarga
        $transaccion = $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 100.00,
            referencia: 'RECARGA-003'
        );

        // Verificar que la transacción está asociada a la billetera correcta
        $this->assertEquals($billeteraId, $transaccion->getBilletera()->getId());

        // Verificar que se puede recuperar desde BD
        $this->entityManager->clear();
        $transaccionRecuperada = $this->entityManager
            ->getRepository(Transaccion::class)
            ->find($transaccion->getId());

        $this->assertNotNull($transaccionRecuperada, 'La transacción debe existir en BD');
        $this->assertEquals('recarga', $transaccionRecuperada->getTipo());
        $this->assertEquals('100.00', $transaccionRecuperada->getMonto());
        $this->assertEquals('RECARGA-003', $transaccionRecuperada->getReferencia());
    }

    /**
     * TEST 2.4: Persistencia en Base de Datos
     * Verificar que tanto la transacción como el saldo se guardan permanentemente
     */
    public function testRecargaBilleteraPersistenteEnBD(): void
    {
        // Crear cliente
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        // Ejecutar recarga
        $transaccion = $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 25.75,
            referencia: 'RECARGA-004'
        );

        $transaccionId = $transaccion->getId();

        // Limpiar cache completamente
        $this->entityManager->clear();

        // Verificar que la billetera tiene el saldo actualizado
        $billeteraRecuperada = $this->entityManager
            ->getRepository(Billetera::class)
            ->find($billeteraId);

        $this->assertEquals('25.75', $billeteraRecuperada->getSaldo(),
            'El saldo debe persistir en BD');

        // Verificar que la transacción existe
        $transaccionRecuperada = $this->entityManager
            ->getRepository(Transaccion::class)
            ->find($transaccionId);

        $this->assertNotNull($transaccionRecuperada, 'La transacción debe persistir en BD');
        $this->assertEquals('recarga', $transaccionRecuperada->getTipo());
        $this->assertEquals('25.75', $transaccionRecuperada->getMonto());
    }

    /**
     * TEST 2.5: Cliente No Encontrado
     * Verificar que maneja correctamente el error cuando el cliente no existe
     */
    public function testRecargaBilleteraClienteNoEncontrado(): void
    {
        // Intentar recargar billetera de cliente que no existe
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Billetera no encontrada para el cliente");

        $this->walletService->recargaBilletera(
            clienteId: 99999, // ID que no existe
            monto: 50.00,
            referencia: 'RECARGA-005'
        );
    }

    /**
     * TEST 2.6: Múltiples Recargas
     * Verificar que se pueden hacer múltiples recargas y los saldos se acumulan
     */
    public function testRecargaMultiplesVeces(): void
    {
        // Crear cliente
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        // Primera recarga: +50.00
        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 50.00,
            referencia: 'RECARGA-006A'
        );

        // Segunda recarga: +25.50
        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 25.50,
            referencia: 'RECARGA-006B'
        );

        // Tercera recarga: +12.25
        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 12.25,
            referencia: 'RECARGA-006C'
        );

        // Limpiar cache
        $this->entityManager->clear();

        // Verificar saldo final
        $billeteraFinal = $this->entityManager
            ->getRepository(Billetera::class)
            ->find($billeteraId);

        $saldoEsperado = number_format(50.00 + 25.50 + 12.25, 2, '.', ''); // 50 + 25.50 + 12.25 = 87.75
        $this->assertEquals($saldoEsperado, $billeteraFinal->getSaldo(),
            'El saldo debe acumular todas las recargas');

        // Verificar que se crearon 3 transacciones
        $transacciones = $this->entityManager
            ->getRepository(Transaccion::class)
            ->findBy(['billetera' => $billeteraId]);

        $this->assertCount(3, $transacciones, 'Deben existir 3 transacciones');

        // Verificar montos individuales
        $montos = array_map(fn($t) => $t->getMonto(), $transacciones);
        $this->assertContains('50.00', $montos);
        $this->assertContains('25.50', $montos);
        $this->assertContains('12.25', $montos);
    }
}
