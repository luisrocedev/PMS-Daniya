<?php
// core/Database.php

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // Cargamos la configuración
        $config = require __DIR__ . '/../config/config.php';
        $dbConfig = $config['db'];

        // Construimos el DSN para PDO
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

        try {
            $this->pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
            // Configuramos manejo de errores en modo EXCEPTION
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // Singleton: única instancia
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Para obtener el objeto PDO y hacer queries
    public function getConnection()
    {
        return $this->pdo;
    }
}
