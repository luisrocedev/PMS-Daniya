<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../core/Database.php';

$pdo = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM campanas_marketing WHERE id_campana = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $pdo->query("SELECT * FROM campanas_marketing ORDER BY fecha_creacion DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO campanas_marketing (nombre, asunto, contenido_html, estado) VALUES (?, ?, ?, ?)");
    $ok = $stmt->execute([
        $data['nombre'], $data['asunto'], $data['contenido_html'], $data['estado'] ?? 'borrador'
    ]);
    echo json_encode(['success' => $ok]);
    exit;
}

if ($method === 'PUT' && $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE campanas_marketing SET nombre=?, asunto=?, contenido_html=?, estado=? WHERE id_campana=?");
    $ok = $stmt->execute([
        $data['nombre'], $data['asunto'], $data['contenido_html'], $data['estado'] ?? 'borrador', $id
    ]);
    echo json_encode(['success' => $ok]);
    exit;
}

if ($method === 'DELETE' && $id) {
    $stmt = $pdo->prepare("DELETE FROM campanas_marketing WHERE id_campana=?");
    $ok = $stmt->execute([$id]);
    echo json_encode(['success' => $ok]);
    exit;
}

echo json_encode(['error' => 'Método no permitido']);
