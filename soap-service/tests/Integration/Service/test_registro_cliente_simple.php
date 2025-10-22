<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Service\WalletService;
use App\Entity\Cliente;
use App\Entity\Billetera;
use Doctrine\ORM\EntityManagerInterface;

// Simple test runner without PHPUnit
echo "=== TEST SIMPLE: registroCliente() ===\n";

try {
    // Boot Symfony kernel
    $kernel = new \App\Kernel('test', true);
    $kernel->boot();
    
    $container = $kernel->getContainer();
    $entityManager = $container->get(EntityManagerInterface::class);
    $walletService = $container->get(WalletService::class);
    
    echo "✅ Kernel booted successfully\n";
    
    // Limpiar BD
    $connection = $entityManager->getConnection();
    $connection->executeStatement('DELETE FROM transacciones');
    $connection->executeStatement('DELETE FROM pagos_pendientes');
    $connection->executeStatement('DELETE FROM billeteras');
    $connection->executeStatement('DELETE FROM clientes');
    echo "✅ Database cleaned\n";
    
    // Test 1: Registro exitoso
    echo "\n🧪 Test 1: Registro exitoso\n";
    $cliente = $walletService->registroCliente(
        tipoDocumento: 'CC',
        numeroDocumento: '1234567890',
        nombres: 'Juan',
        apellidos: 'Pérez',
        email: 'juan@example.com',
        celular: '3001234567'
    );
    
    // Validaciones
    if ($cliente instanceof Cliente) {
        echo "✅ Cliente creado correctamente\n";
    } else {
        throw new Exception("❌ Cliente no es instancia de Cliente");
    }
    
    if ($cliente->getId() !== null) {
        echo "✅ Cliente tiene ID: " . $cliente->getId() . "\n";
    } else {
        throw new Exception("❌ Cliente no tiene ID");
    }
    
    if ($cliente->getNombres() === 'Juan') {
        echo "✅ Nombre correcto: " . $cliente->getNombres() . "\n";
    } else {
        throw new Exception("❌ Nombre incorrecto: " . $cliente->getNombres());
    }
    
    if ($cliente->getEmail() === 'juan@example.com') {
        echo "✅ Email correcto: " . $cliente->getEmail() . "\n";
    } else {
        throw new Exception("❌ Email incorrecto: " . $cliente->getEmail());
    }
    
    // Test 2: Billetera creada
    echo "\n🧪 Test 2: Billetera creada\n";
    $billetera = $cliente->getBilletera();
    
    if ($billetera !== null) {
        echo "✅ Billetera asociada al cliente\n";
    } else {
        throw new Exception("❌ Cliente no tiene billetera asociada");
    }
    
    if ($billetera->getSaldo() === '0.00') {
        echo "✅ Saldo inicial correcto: " . $billetera->getSaldo() . "\n";
    } else {
        throw new Exception("❌ Saldo inicial incorrecto: " . $billetera->getSaldo());
    }
    
    if ($billetera->getId() !== null) {
        echo "✅ Billetera tiene ID: " . $billetera->getId() . "\n";
    } else {
        throw new Exception("❌ Billetera no tiene ID");
    }
    
    // Test 3: Persistencia en BD
    echo "\n🧪 Test 3: Persistencia en BD\n";
    $entityManager->clear(); // Limpiar cache
    
    $clienteRecuperado = $entityManager->getRepository(Cliente::class)->find($cliente->getId());
    
    if ($clienteRecuperado !== null) {
        echo "✅ Cliente recuperado desde BD\n";
    } else {
        throw new Exception("❌ No se pudo recuperar cliente desde BD");
    }
    
    if ($clienteRecuperado->getEmail() === 'juan@example.com') {
        echo "✅ Email persistido correctamente: " . $clienteRecuperado->getEmail() . "\n";
    } else {
        throw new Exception("❌ Email no persistido correctamente");
    }
    
    $billeteraRecuperada = $clienteRecuperado->getBilletera();
    if ($billeteraRecuperada !== null && $billeteraRecuperada->getSaldo() === '0.00') {
        echo "✅ Billetera persistida correctamente con saldo: " . $billeteraRecuperada->getSaldo() . "\n";
    } else {
        throw new Exception("❌ Billetera no persistida correctamente");
    }
    
    echo "\n🎉 TODOS LOS TESTS PASARON EXITOSAMENTE\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} catch (\Throwable $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}