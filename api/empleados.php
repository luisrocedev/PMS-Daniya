<?php
// api/empleados.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$superModel = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];

// Si llega ?id=XX en la query
$id = $_GET['id'] ?? 0;

/**
 * CRUD con filtros y paginación:
 *   GET    => /empleados.php
 *        Parámetros opcionales:
 *           - search (texto a buscar en nombre/apellidos/dni)
 *           - rol (id_rol)
 *           - dep (id_departamento)
 *           - page (página)
 *           - limit (cantidad de registros por página)
 *   POST   => /empleados.php        => Crear un empleado
 *   PUT    => /empleados.php?id=XX  => Actualizar un empleado
 *   DELETE => /empleados.php?id=XX  => Eliminar un empleado
 */

if ($method === 'GET') {
    // FILTROS Y PAGINACIÓN
    $search = $_GET['search'] ?? '';   // texto de búsqueda
    $rol    = $_GET['rol']    ?? '';   // id_rol
    $dep    = $_GET['dep']    ?? '';   // id_departamento
    $page   = $_GET['page']   ?? 1;
    $limit  = $_GET['limit']  ?? 5;

    $page  = (int)$page;
    $limit = (int)$limit;
    if ($page < 1)  $page  = 1;
    if ($limit < 1) $limit = 5;

    $offset = ($page - 1) * $limit;

    // Si pidieron un empleado específico (GET /empleados.php?id=XX)
    if ($id) {
        $empleado = $superModel->getById('empleados', $id);
        if ($empleado) {
            // Obtener nombre de rol y departamento
            $pdo = Database::getInstance()->getConnection();
            $sql = "SELECT r.nombre_rol, d.nombre_departamento FROM empleados e LEFT JOIN roles r ON e.id_rol = r.id_rol LEFT JOIN departamentos d ON e.id_departamento = d.id_departamento WHERE e.id_empleado = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $extra = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($extra) {
                $empleado['nombre_rol'] = $extra['nombre_rol'];
                $empleado['nombre_departamento'] = $extra['nombre_departamento'];
            }
        }
        echo json_encode($empleado);
        exit;
    } else {
        // Permitir alias 'departamento' para compatibilidad con JS
        if (isset($_GET['departamento']) && !isset($_GET['dep'])) {
            $dep = $_GET['departamento'];
        }
        // Construimos la clausula WHERE dinámica
        $where = " WHERE 1=1 ";
        $params = [];

        if ($search) {
            $where .= " AND (nombre LIKE :search OR apellidos LIKE :search OR dni LIKE :search) ";
            $params[':search'] = "%$search%";
        }
        if ($rol) {
            $where .= " AND id_rol = :rol ";
            $params[':rol'] = $rol;
        }
        if ($dep) {
            $where .= " AND id_departamento = :dep ";
            $params[':dep'] = $dep;
        }

        $pdo = Database::getInstance()->getConnection();
        $sqlCount = "SELECT COUNT(*) as total FROM empleados $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // JOIN para obtener nombre de rol y departamento
        $sql = "SELECT e.*, r.nombre_rol, d.nombre_departamento FROM empleados e LEFT JOIN roles r ON e.id_rol = r.id_rol LEFT JOIN departamentos d ON e.id_departamento = d.id_departamento $where LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'data'  => $empleados,
            'total' => (int)$total,
            'page'  => $page,
            'limit' => $limit
        ]);
        exit;
    }
} elseif ($method === 'POST') {
    // Crear un nuevo empleado
    $nombre          = $_POST['nombre']          ?? '';
    $apellidos       = $_POST['apellidos']       ?? '';
    $dni             = $_POST['dni']             ?? '';
    $telefono        = $_POST['telefono']        ?? '';
    $email           = $_POST['email']           ?? '';
    $direccion       = $_POST['direccion']       ?? '';
    $fecha_contrat   = $_POST['fecha_contrat']   ?? '';
    $id_rol          = $_POST['id_rol']          ?? '';
    $id_departamento = $_POST['id_departamento'] ?? '';

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
    try {
        if (!$id) {
            echo json_encode(['error' => 'Falta id para actualizar']);
            exit;
        }
        parse_str(file_get_contents("php://input"), $input);
        $ok = $superModel->update('empleados', $id, $input);
        if ($ok) {
            echo json_encode(['success' => true, 'msg' => 'Empleado actualizado']);
        } else {
            echo json_encode(['error' => 'No se pudo actualizar el empleado']);
        }
    } catch (Throwable $e) {
        // Detectar error de clave foránea (MySQL 1451)
        if (strpos($e->getMessage(), '1451') !== false) {
            echo json_encode(['error' => 'No se puede editar este empleado porque tiene registros asociados (por ejemplo, asistencia).']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
    exit;
} elseif ($method === 'DELETE') {
    // Eliminar
    try {
        if (!$id) {
            echo json_encode(['error' => 'Falta id para eliminar']);
            exit;
        }
        $ok = $superModel->delete('empleados', $id);
        if ($ok) {
            echo json_encode(['success' => true, 'msg' => 'Empleado eliminado']);
        } else {
            echo json_encode(['error' => 'No se pudo eliminar el empleado']);
        }
    } catch (Throwable $e) {
        // Detectar error de clave foránea (MySQL 1451)
        if (strpos($e->getMessage(), '1451') !== false) {
            echo json_encode(['error' => 'No se puede eliminar este empleado porque tiene registros asociados (por ejemplo, asistencia).']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
