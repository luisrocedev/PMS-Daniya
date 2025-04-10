<?php
// api/empleados.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$superModel = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];

// Leer ID si viene en la query (ej: /empleados.php?id=3)
$id = $_GET['id'] ?? 0;

/**
 * CRUD:
 *   GET    => /empleados.php        => Listar todos
 *   GET    => /empleados.php?id=XX  => Obtener un empleado
 *   POST   => /empleados.php        => Crear un empleado
 *   PUT    => /empleados.php?id=XX  => Actualizar un empleado
 *   DELETE => /empleados.php?id=XX  => Eliminar un empleado
 */

if ($method === 'GET') {
    if ($id) {
        // Obtener un empleado concreto
        $empleado = $superModel->getById('empleados', $id);
        echo json_encode($empleado);
    } else {
        // Obtener todos los empleados
        $empleados = $superModel->getAll('empleados');
        echo json_encode($empleados);
    }
    exit;
} elseif ($method === 'POST') {
    // Crear un nuevo empleado
    // Recibir datos por $_POST (o JSON, según tu caso)
    $nombre          = $_POST['nombre']          ?? '';
    $apellidos       = $_POST['apellidos']       ?? '';
    $dni             = $_POST['dni']             ?? '';
    $telefono        = $_POST['telefono']        ?? '';
    $email           = $_POST['email']           ?? '';
    $direccion       = $_POST['direccion']       ?? '';
    $fecha_contrat   = $_POST['fecha_contrat']   ?? ''; // fecha_contratacion
    $id_rol          = $_POST['id_rol']          ?? '';
    $id_departamento = $_POST['id_departamento'] ?? '';

    // Armar array con los campos de la tabla
    $data = [
        'nombre'           => $nombre,
        'apellidos'        => $apellidos,
        'dni'              => $dni,
        'telefono'         => $telefono,
        'email'            => $email,
        'direccion'        => $direccion,
        'fecha_contratacion' => $fecha_contrat,
        'id_rol'           => $id_rol,
        'id_departamento'  => $id_departamento
    ];

    $ok = $superModel->create('empleados', $data);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Empleado creado con éxito']);
    } else {
        echo json_encode(['error' => 'No se pudo crear el empleado']);
    }
    exit;
} elseif ($method === 'PUT') {
    // Actualizar
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    // parse_str => para leer los datos de php://input (PUT)
    parse_str(file_get_contents("php://input"), $input);

    $ok = $superModel->update('empleados', $id, $input);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Empleado actualizado']);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar el empleado']);
    }
    exit;
} elseif ($method === 'DELETE') {
    // Eliminar
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('empleados', $id);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Empleado eliminado']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar el empleado']);
    }
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
