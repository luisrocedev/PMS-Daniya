<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Consulta de roles
$pdo = Database::getInstance()->getConnection();
$sql = "SELECT * FROM roles";
$stmt = $pdo->query($sql);
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retornamos la lista en JSON
echo json_encode($roles);
