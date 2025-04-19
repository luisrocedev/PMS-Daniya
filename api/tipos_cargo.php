<?php
// api/tipos_cargo.php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

$tipos = $pdo->query("SELECT id_tipo, nombre, precio FROM tipo_cargo ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($tipos);
