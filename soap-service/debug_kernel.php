<?php

$_SERVER['APP_ENV'] = 'test';
$_SERVER['SHELL_VERBOSITY'] = '-1';
$_SERVER['KERNEL_CLASS'] = 'App\Kernel';

require 'vendor/autoload.php';

$kernel = new App\Kernel('test', true);
$kernel->boot();

echo 'Kernel env: ' . $kernel->getEnvironment() . PHP_EOL;
echo 'Kernel debug: ' . ($kernel->isDebug() ? 'YES' : 'NO') . PHP_EOL;
echo 'Has test.service_container: ' . ($kernel->getContainer()->has('test.service_container') ? 'YES' : 'NO') . PHP_EOL;

$container = $kernel->getContainer();
$services = $container->getServiceIds();
$testServices = array_filter($services, fn($s) => strpos($s, 'test') === 0);
echo 'Test services available: ' . count($testServices) . PHP_EOL;
if (count($testServices) > 0) {
    foreach (array_slice($testServices, 0, 5) as $svc) {
        echo "  - $svc" . PHP_EOL;
    }
}
