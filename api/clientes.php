<?php
// api/clientes.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

// Verificamos sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$superModel = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];

// Leer ID si viene en la query
$id = $_GET['id'] ?? 0;

/**
 * Estructura CRUD:
 * GET    => /clientes.php        => Listar todos
 * GET    => /clientes.php?id=XX  => Obtener uno
 * POST   => /clientes.php        => Crear cliente (con datos en $_POST)
 * PUT    => /clientes.php?id=XX  => Actualizar un cliente
 * DELETE => /clientes.php?id=XX  => Eliminar un cliente
 */

if ($method === 'GET') {
    if ($id) {
        // Retornar un cliente
        $cliente = $superModel->getById('clientes', $id);
        echo json_encode($cliente);
    } else {
        // Retornar todos los clientes
        $clientes = $superModel->getAll('clientes');
        echo json_encode($clientes);
    }
    exit;
} elseif ($method === 'POST') {
    // Crear un nuevo cliente
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';

    // Puedes incluir más columnas según tu estructura de la BD
    // Asumiendo que en tu tabla `clientes` existen columnas: 
    // nombre, apellidos, telefono, email, etc.

    $data = [
        'nombre'    => $nombre,
        'apellidos' => $apellidos,
        'telefono'  => $telefono,
        'email'     => $email
    ];

    $ok = $superModel->create('clientes', $data);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Cliente creado con éxito']);
    } else {
        echo json_encode(['error' => 'No se pudo crear el cliente']);
    }
    exit;
} elseif ($method === 'PUT') {
    // Actualizar un cliente
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    // parse_str => para leer datos de php://input
    parse_str(file_get_contents("php://input"), $input);

    $ok = $superModel->update('clientes', $id, $input);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Cliente actualizado']);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar el cliente']);
    }
    exit;
} elseif ($method === 'DELETE') {
    // Eliminar un cliente
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('clientes', $id);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Cliente eliminado']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar el cliente']);
    }
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
