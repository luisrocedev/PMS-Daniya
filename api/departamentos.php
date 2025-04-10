<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Consulta de departamentos
$pdo = Database::getInstance()->getConnection();
$sql = "SELECT * FROM departamentos";
$stmt = $pdo->query($sql);
$deps = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($deps);
