<?php

namespace App\Tests\Integration\Service;

use App\Entity\Cliente;
use App\Entity\Billetera;
use App\Service\WalletService;
use App\Constants\ErrorCodes;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class RegistroClienteTest extends KernelTestCase
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
     * TEST 1.1: Happy Path - Registro Exitoso
     * Verificar que se crea Cliente + Billetera exitosamente
     */
    public function testRegistroClienteExitoso(): void
    {
        $cliente = $this->walletService->registroCliente(
            tipoDocumento: 'CC',
            numeroDocumento: '1234567890',
            nombres: 'Juan',
            apellidos: 'Pérez',
            email: 'juan@example.com',
            celular: '3001234567'
        );

        // Validar que la respuesta es correcta
        $this->assertIsArray($cliente);
        $this->assertTrue($cliente['success']);
        $this->assertEquals(ErrorCodes::SUCCESS, $cliente['cod_error']);
        $this->assertEquals('Cliente registrado exitosamente', $cliente['message_error']);
        $this->assertIsArray($cliente['data']);

        $data = $cliente['data'];

        // Validar que el cliente fue creado correctamente
        $this->assertNotNull($data['id'], 'El cliente debe tener un ID generado');
        $this->assertEquals('CC', $data['tipoDocumento']);
        $this->assertEquals('1234567890', $data['numeroDocumento']);
        $this->assertEquals('Juan', $data['nombres']);
        $this->assertEquals('Pérez', $data['apellidos']);
        $this->assertEquals('juan@example.com', $data['email']);
        $this->assertEquals('3001234567', $data['celular']);

        // Validar que la fecha de registro es reciente (≤ 1 segundo)
        $fechaRegistro = new \DateTime($data['fechaRegistro']);
        $this->assertInstanceOf(\DateTimeInterface::class, $fechaRegistro);
        $ahora = new \DateTime();
        $diferencia = abs($ahora->getTimestamp() - $fechaRegistro->getTimestamp());
        $this->assertLessThanOrEqual(1, $diferencia, 'La fecha de registro debe ser reciente');
    }

    /**
     * TEST 1.2: Billetera Creada Automáticamente
     * Verificar que se crea Billetera asociada al Cliente
     */
    public function testRegistroClienteCreaBilletera(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );

        $this->assertTrue($response['success']);
        $data = $response['data'];
        $billeteraData = $data['billetera'];
        
        // Validar que la billetera fue creada
        $this->assertNotNull($billeteraData, 'El cliente debe tener una billetera asociada');
        $this->assertIsArray($billeteraData);
        $this->assertNotNull($billeteraData['id'], 'La billetera debe tener un ID generado');

        // Validar saldo inicial
        $this->assertEquals('0.00', $billeteraData['saldo'], 'La billetera debe iniciar con saldo 0.00');

        // Validar que la billetera existe y tiene ID válido
        $this->assertIsInt($billeteraData['id'], 'La billetera debe tener un ID entero válido');

        // Validar fecha de creación
        $fechaCreacion = new \DateTime($billeteraData['fechaCreacion']);
        $this->assertInstanceOf(\DateTimeInterface::class, $fechaCreacion);
        $ahora = new \DateTime();
        $diferencia = abs($ahora->getTimestamp() - $fechaCreacion->getTimestamp());
        $this->assertLessThanOrEqual(1, $diferencia, 'La fecha de creación debe ser reciente');
    }

    /**
     * TEST 1.3: Persistencia en Base de Datos
     * Verificar que los datos se guardaron realmente en BD (no solo en memoria)
     */
    public function testRegistroClientePersistenteEnBD(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );

        $this->assertTrue($response['success']);
        $clienteId = $response['data']['id'];
        
        // Limpiar cache para forzar fresh query
        $this->entityManager->clear();
        
        // Buscar cliente desde BD
        $clienteRecuperado = $this->entityManager
            ->getRepository(Cliente::class)
            ->find($clienteId);
        
        $this->assertNotNull($clienteRecuperado, 'El cliente debe existir en la base de datos');
        $this->assertEquals('Juan', $clienteRecuperado->getNombres());
        $this->assertEquals('Pérez', $clienteRecuperado->getApellidos());
        $this->assertEquals('juan@example.com', $clienteRecuperado->getEmail());
        $this->assertEquals('3001234567', $clienteRecuperado->getCelular());
        $this->assertEquals('CC', $clienteRecuperado->getTipoDocumento());
        $this->assertEquals('1234567890', $clienteRecuperado->getNumeroDocumento());
        
        // Verificar que la billetera también existe en BD
        $billeteraRecuperada = $clienteRecuperado->getBilletera();
        $this->assertNotNull($billeteraRecuperada, 'La billetera debe existir en la base de datos');
        $this->assertEquals('0.00', $billeteraRecuperada->getSaldo());
    }

    /**
     * TEST 1.4: Datos Únicos (Email Duplicado)
     * Verificar que no se permite registrar dos clientes con el mismo email
     */
    public function testRegistroClienteConEmailDuplicadoFalla(): void
    {
        // Registrar primer cliente
        $response1 = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );
        $this->assertTrue($response1['success']);

        // Intentar registrar segundo cliente con mismo email
        $response2 = $this->walletService->registroCliente(
            'CC', '0987654321', 'Pedro', 'López', 'juan@example.com', '3009876543'
        );

        // Validar respuesta de error
        $this->assertFalse($response2['success'], 'El registro debe fallar');
        $this->assertEquals(ErrorCodes::CLIENTE_DUPLICADO, $response2['cod_error']);
        $this->assertEquals('El correo electrónico ya está registrado en el sistema', $response2['message_error']);
        $this->assertEmpty($response2['data']);
    }

    /**
     * TEST 1.5: Tipos de Documento Diferentes
     * Verificar que soporta diferentes tipos (CC, CE, TI, etc.)
     */
    public function testRegistroClienteConTiposDocumentoDiferentes(): void
    {
        $responseCC = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );

        $responseCE = $this->walletService->registroCliente(
            'CE', '9876543210', 'Pedro', 'López', 'pedro@example.com', '3009876543'
        );

        $this->assertTrue($responseCC['success']);
        $this->assertTrue($responseCE['success']);

        $clienteCC = $responseCC['data'];
        $clienteCE = $responseCE['data'];

        $this->assertEquals('CC', $clienteCC['tipoDocumento']);
        $this->assertEquals('1234567890', $clienteCC['numeroDocumento']);
        $this->assertEquals('CE', $clienteCE['tipoDocumento']);
        $this->assertEquals('9876543210', $clienteCE['numeroDocumento']);
    }

    /**
     * TEST 1.6: Múltiples Registros Independientes
     * Verificar que no hay interferencia entre registros
     */
    public function testRegistroMultiplesClientesIndependientes(): void
    {
        $response1 = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );

        $response2 = $this->walletService->registroCliente(
            'CC', '0987654321', 'Pedro', 'López', 'pedro@example.com', '3009876543'
        );

        $this->assertTrue($response1['success']);
        $this->assertTrue($response2['success']);

        $cliente1Data = $response1['data'];
        $cliente2Data = $response2['data'];

        // Verificar que son clientes diferentes
        $this->assertNotEquals($cliente1Data['id'], $cliente2Data['id'], 'Los clientes deben tener IDs diferentes');

        // Verificar que sus billeteras son diferentes
        $this->assertNotEquals(
            $cliente1Data['billetera']['id'],
            $cliente2Data['billetera']['id'],
            'Las billeteras deben tener IDs diferentes'
        );

        // Verificar datos de cada uno
        $this->assertEquals('juan@example.com', $cliente1Data['email']);
        $this->assertEquals('pedro@example.com', $cliente2Data['email']);
        $this->assertEquals('Juan', $cliente1Data['nombres']);
        $this->assertEquals('Pedro', $cliente2Data['nombres']);
    }

    /**
     * TEST 1.7: Validación - Celular Vacío
     * Verificar que rechaza registros con celular vacío
     */
    public function testRegistroClienteCelularVacioFalla(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', ''
        );

        $this->assertFalse($response['success']);
        $this->assertEquals(ErrorCodes::CAMPOS_REQUERIDOS, $response['cod_error']);
        $this->assertStringContainsString('requerido', strtolower($response['message_error']));
        $this->assertEmpty($response['data']);
    }

    /**
     * TEST 1.8: Validación - Email Vacío
     * Verificar que rechaza registros con email vacío
     */
    public function testRegistroClienteEmailVacioFalla(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', '', '3001234567'
        );

        $this->assertFalse($response['success']);
        $this->assertEquals(ErrorCodes::CAMPOS_REQUERIDOS, $response['cod_error']);
        $this->assertStringContainsString('requerido', strtolower($response['message_error']));
        $this->assertEmpty($response['data']);
    }

    /**
     * TEST 1.9: Validación - Email Inválido
     * Verificar que rechaza emails con formato inválido
     */
    public function testRegistroClienteEmailInvalidoFalla(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'email_invalido', '3001234567'
        );

        $this->assertFalse($response['success']);
        $this->assertEquals(ErrorCodes::CAMPOS_REQUERIDOS, $response['cod_error']);
        $this->assertStringContainsString('válido', $response['message_error']);
        $this->assertEmpty($response['data']);
    }

    /**
     * TEST 1.10: Validación - Celular con Formato Inválido
     * Verificar que rechaza celulares que no sean 10 dígitos
     */
    public function testRegistroClienteCelularFormatoInvalidoFalla(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', 'Juan', 'Pérez', 'juan@example.com', '300123'
        );

        $this->assertFalse($response['success']);
        $this->assertEquals(ErrorCodes::CAMPOS_REQUERIDOS, $response['cod_error']);
        $this->assertStringContainsString('10', $response['message_error']);
        $this->assertEmpty($response['data']);
    }

    /**
     * TEST 1.11: Validación - Nombre Vacío
     * Verificar que rechaza registros con nombre vacío
     */
    public function testRegistroClienteNombreVacioFalla(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '1234567890', '', 'Pérez', 'juan@example.com', '3001234567'
        );

        $this->assertFalse($response['success']);
        $this->assertEquals(ErrorCodes::CAMPOS_REQUERIDOS, $response['cod_error']);
        $this->assertStringContainsString('requerido', strtolower($response['message_error']));
        $this->assertEmpty($response['data']);
    }

    /**
     * TEST 1.12: Validación - Documento Vacío
     * Verificar que rechaza registros con documento vacío
     */
    public function testRegistroClienteDocumentoVacioFalla(): void
    {
        $response = $this->walletService->registroCliente(
            'CC', '', 'Juan', 'Pérez', 'juan@example.com', '3001234567'
        );

        $this->assertFalse($response['success']);
        $this->assertEquals(ErrorCodes::CAMPOS_REQUERIDOS, $response['cod_error']);
        $this->assertStringContainsString('requerido', strtolower($response['message_error']));
        $this->assertEmpty($response['data']);
    }
}
