<?php
// config/config.php

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno desde .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

return [
    'db' => [
        'host'     => $_ENV['DB_HOST'],
        'dbname'   => $_ENV['DB_NAME'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'charset'  => $_ENV['DB_CHARSET']
    ],
    'app' => [
        'debug' => filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN)
    ],
    'smtp' => [
        'host'     => $_ENV['SMTP_HOST'],
        'port'     => $_ENV['SMTP_PORT'],
        'username' => $_ENV['SMTP_USERNAME'],
        'password' => $_ENV['SMTP_PASSWORD'],
        'from_email' => $_ENV['SMTP_FROM_EMAIL'],
        'from_name'  => $_ENV['SMTP_FROM_NAME']
    ]
];
