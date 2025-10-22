<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] ?? 'dev';
putenv('APP_ENV=' . $_ENV['APP_ENV']);

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
    if ($_ENV['APP_ENV'] === 'test') {
        (new Dotenv())->load(dirname(__DIR__).'/.env.test');
    }
}

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0000);
}
