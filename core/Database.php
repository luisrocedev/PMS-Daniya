<?php
// core/Database.php

require_once __DIR__ . '/../vendor/autoload.php';

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            // Intentar cargar variables de entorno desde .env
            if (file_exists(__DIR__ . '/../.env')) {
                $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
                $dotenv->load();
            }

            // Usar valores por defecto si no hay .env
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'pms_daniya_denia';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            // Construir DSN para PDO
            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

            $this->pdo = new PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Error de conexión a la base de datos']);
            } else {
                echo "Error de conexión a la base de datos";
            }
            exit;
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
