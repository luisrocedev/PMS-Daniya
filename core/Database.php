<?php
// core/Database.php

require_once __DIR__ . '/../vendor/autoload.php'; // Cargar dotenv

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // Cargar variables de entorno desde .env
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // Asegúrate de que apunte al directorio raíz
        $dotenv->load();

        // Construir DSN para PDO
        $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=" . $_ENV['DB_CHARSET'];

        try {
            $this->pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
