<?php
// api/clientes.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';
require_once __DIR__ . '/../core/FunnelLogic.php'; // Para enviar email

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$superModel = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? 0;

/**
 * Rutas:
 * GET => /api/clientes.php
 *   - Parámetros opcionales:
 *       search (filtra por nombre, apellidos, dni)
 *       page, limit (paginación)
 *   - O bien /api/clientes.php?id=XX para un solo cliente
 *
 * POST => Crear un cliente
 * PUT => Actualizar un cliente (con ?id=XX)
 * DELETE => Eliminar un cliente (con ?id=XX)
 */

if ($method === 'GET') {
    // Búsqueda y paginación
    $search = $_GET['search'] ?? '';
    $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 5;
    $offset = ($page - 1) * $limit;

    if ($id) {
        // Un solo cliente
        $cliente = $superModel->getById('clientes', $id);
        echo json_encode($cliente);
        exit;
    } else {
        // WHERE dinámico
        $where = " WHERE 1=1 ";
        $params = [];

        if ($search) {
            $where .= " AND (nombre LIKE :s OR apellidos LIKE :s OR dni LIKE :s) ";
            $params[':s'] = "%$search%";
        }

        // Contar total
        $pdo = Database::getInstance()->getConnection();
        $sqlCount = "SELECT COUNT(*) AS total FROM clientes $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = $stmtCount->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

        // Consulta principal con LIMIT/OFFSET
        $sql = "SELECT * FROM clientes $where LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode([
            'data'  => $data,
            'total' => (int)$total,
            'page'  => $page,
            'limit' => $limit
        ]);
        exit;
    }
} elseif ($method === 'POST') {
    // Crear
    $nombre     = $_POST['nombre']     ?? '';
    $apellidos  = $_POST['apellidos']  ?? '';
    $dni        = $_POST['dni']        ?? '';
    $email      = $_POST['email']      ?? '';
    $telefono   = $_POST['telefono']   ?? '';
    $direccion  = $_POST['direccion']  ?? '';
    // Aquí capturamos el estado_funnel si viene en POST
    $estadoFunnel = $_POST['estado_funnel'] ?? null;

    // Insertar en BD
    $ok = $superModel->create('clientes', [
        'nombre'        => $nombre,
        'apellidos'     => $apellidos,
        'dni'           => $dni,
        'email'         => $email,
        'telefono'      => $telefono,
        'direccion'     => $direccion,
        // 'estado_funnel' => la columna debe existir en BD
        'estado_funnel' => $estadoFunnel
    ]);

    if ($ok) {
        // Si tenemos un estado_funnel, se envía email "bonito" con la nueva plantilla
        if ($estadoFunnel) {
            FunnelLogic::enviarEmailFunnel([
                'nombre'        => $nombre,
                'email'         => $email,
                'estado_funnel' => $estadoFunnel
            ]);
        }
        echo json_encode(['success' => true, 'msg' => 'Cliente creado con éxito']);
    } else {
        echo json_encode(['error' => 'No se pudo crear el cliente']);
    }
    exit;
} elseif ($method === 'PUT') {
    // Actualizar
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $input);

    $ok = $superModel->update('clientes', $id, $input);

    if ($ok) {
        // Si en $input se recibió estado_funnel, reenviamos correo
        if (!empty($input['estado_funnel'])) {
            $clienteActual = $superModel->getById('clientes', $id);
            if ($clienteActual) {
                FunnelLogic::enviarEmailFunnel([
                    'nombre'        => $clienteActual['nombre'] ?? '',
                    'email'         => $clienteActual['email']  ?? '',
                    'estado_funnel' => $clienteActual['estado_funnel'] ?? 'Nuevo'
                ]);
            }
        }
        echo json_encode(['success' => true, 'msg' => 'Cliente actualizado']);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar']);
    }
    exit;
} elseif ($method === 'DELETE') {
    // Eliminar
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('clientes', $id);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Cliente eliminado']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar']);
    }
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
