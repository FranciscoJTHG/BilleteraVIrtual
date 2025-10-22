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

    /**
     * TEST 3.1: Happy Path - Pago Exitoso
     * Verificar que se crea un pago pendiente correctamente con token y expiración
     */
    public function testPagarExitoso(): void
    {
        // Crear cliente con saldo
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];

        // Recargar billetera con $100
        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 100.00,
            referencia: 'RECARGA-TEST-001'
        );

        // Ejecutar pago de $50
        $resultado = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 50.00,
            descripcion: 'Pago de prueba'
        );

        // Validar estructura de respuesta
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('sessionId', $resultado);
        $this->assertArrayHasKey('monto', $resultado);
        $this->assertArrayHasKey('expiresAt', $resultado);

        // Validar datos
        $this->assertNotEmpty($resultado['sessionId'], 'El sessionId no debe estar vacío');
        $this->assertEquals(50.00, $resultado['monto'], 'El monto debe ser 50.00');

        // Validar que el sessionId es un UUID válido
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $resultado['sessionId'],
            'El sessionId debe ser un UUID válido'
        );

        // Validar expiración (debe ser string en formato Y-m-d H:i:s)
        $this->assertIsString($resultado['expiresAt']);
        $expiresAt = \DateTime::createFromFormat('Y-m-d H:i:s', $resultado['expiresAt']);
        $this->assertInstanceOf(\DateTime::class, $expiresAt, 'La expiración debe ser una fecha válida');

        // Validar que expira en aproximadamente 15 minutos
        $ahora = new \DateTime();
        $diferencia = $expiresAt->getTimestamp() - $ahora->getTimestamp();
        $this->assertGreaterThan(14 * 60, $diferencia, 'Debe expirar en más de 14 minutos');
        $this->assertLessThan(16 * 60, $diferencia, 'Debe expirar en menos de 16 minutos');
    }

    /**
     * TEST 3.2: Saldo Insuficiente
     * Verificar que rechaza pagos cuando no hay saldo disponible
     */
    public function testPagarSaldoInsuficiente(): void
    {
        // Crear cliente con poco saldo
        $response = $this->walletService->registroCliente(
            'CC', '9876543210', 'María', 'García', 'maria@example.com', '3009876543'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];

        // Recargar billetera con solo $10
        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 10.00,
            referencia: 'RECARGA-TEST-002'
        );

        // Intentar pagar $50 (más que el saldo disponible)
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Saldo insuficiente');

        $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 50.00,
            descripcion: 'Pago que no puede realizarse'
        );
    }

    /**
     * TEST 3.3: Creación de PagoPendiente en Base de Datos
     * Verificar que se persiste correctamente en la tabla pagos_pendientes
     */
    public function testPagarCreaPagoPendienteEnBD(): void
    {
        // Crear cliente con saldo
        $response = $this->walletService->registroCliente(
            'CC', '5555555555', 'Carlos', 'López', 'carlos@example.com', '3005555555'
        );
        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        $billeteraId = $response['data']['billetera']['id'];

        // Recargar billetera
        $this->walletService->recargaBilletera(
            clienteId: $clienteId,
            monto: 200.00,
            referencia: 'RECARGA-TEST-003'
        );

        // Ejecutar pago
        $resultado = $this->walletService->pagar(
            clienteId: $clienteId,
            monto: 75.00,
            descripcion: 'Pago de servicios'
        );

        $sessionId = $resultado['sessionId'];

        // Limpiar cache para forzar fresh query
        $this->entityManager->clear();

        // Recuperar PagoPendiente desde BD
        $pagoPendiente = $this->entityManager
            ->getRepository(PagoPendiente::class)
            ->findOneBy(['sessionId' => $sessionId]);

        // Validar que existe
        $this->assertNotNull($pagoPendiente, 'El PagoPendiente debe existir en BD');

        // Validar propiedades
        $this->assertEquals($billeteraId, $pagoPendiente->getBilletera()->getId());
        $this->assertEquals(75.00, $pagoPendiente->getMonto());
        $this->assertEquals('pendiente', $pagoPendiente->getEstado());
        $this->assertEquals('Pago de servicios', $pagoPendiente->getDescripcion());

        // Validar que la fecha de expiración es en el futuro
        $ahora = new \DateTime();
        $this->assertGreaterThan($ahora, $pagoPendiente->getFechaExpiracion(),
            'La fecha de expiración debe ser en el futuro');
    }
}
