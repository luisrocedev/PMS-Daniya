<?php
// api/reservas.php
header('Content-Type: application/json');
session_start();

try {
    require_once __DIR__ . '/../core/Database.php';
    require_once __DIR__ . '/../core/SuperModel.php';

    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }

    $superModel = new SuperModel();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $pdo = Database::getInstance()->getConnection();

    /**
     * Estados: 
     *  - Pendiente
     *  - Confirmada
     *  - Cancelada
     *  - CheckIn
     *  - CheckOut
     *
     * La función validarTransicionEstado() (definida al final) valida las transiciones permitidas.
     */

    if ($method === 'GET') {
        // Si se pasa un id en la query, se devuelve esa reserva específica
        if ($id) {
            $reserva = $superModel->getById('reservas', $id);
            echo json_encode($reserva);
            exit;
        } else {
            // Permitir búsqueda opcional: search (para id_reserva, id_cliente o id_habitacion), estado y rango de fechas (fecha_entrada)
            $search = $_GET['search'] ?? '';
            $estado = $_GET['estado'] ?? '';
            $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit  = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
            if ($page < 1) {
                $page = 1;
            }
            if ($limit < 1) {
                $limit = 5;
            }
            $offset = ($page - 1) * $limit;

            // Nuevos parámetros para el filtro de rango de fechas en fecha_entrada
            $fechaInicioFiltro = $_GET['fecha_inicio_filtro'] ?? '';
            $fechaFinFiltro    = $_GET['fecha_fin_filtro'] ?? '';

            // Construir la cláusula WHERE de forma dinámica
            $where = " WHERE 1=1 ";
            $params = [];

            if ($search) {
                $where .= " AND (id_reserva LIKE :s OR id_cliente LIKE :s OR id_habitacion LIKE :s) ";
                $params[':s'] = "%$search%";
            }
            if ($estado) {
                $where .= " AND estado_reserva = :estado ";
                $params[':estado'] = $estado;
            }
            if ($fechaInicioFiltro && $fechaFinFiltro) {
                $where .= " AND fecha_entrada BETWEEN :fechaInicio AND :fechaFin ";
                $params[':fechaInicio'] = $fechaInicioFiltro;
                $params[':fechaFin'] = $fechaFinFiltro;
            }

            // Consulta para contar el total
            $sqlCount = "SELECT COUNT(*) as total FROM reservas $where";
            $stmtC = $pdo->prepare($sqlCount);
            foreach ($params as $k => $v) {
                $stmtC->bindValue($k, $v);
            }
            $stmtC->execute();
            $total = $stmtC->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Consulta principal con LIMIT/OFFSET
            $sql = "SELECT * FROM reservas $where LIMIT :lim OFFSET :off";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'data'  => $data,
                'total' => (int)$total,
                'page'  => $page,
                'limit' => $limit
            ]);
            exit;
        }
    } elseif ($method === 'POST') {
        // Crear reserva: se espera recibir:
        // id_cliente, id_habitacion, fecha_entrada, fecha_salida y opcionalmente estado_reserva
        $id_cliente    = $_POST['id_cliente']    ?? '';
        $id_habitacion = $_POST['id_habitacion'] ?? '';
        $fecha_entrada = $_POST['fecha_entrada'] ?? '';
        $fecha_salida  = $_POST['fecha_salida']  ?? '';
        $estado_reserva = $_POST['estado_reserva'] ?? 'Pendiente';

        // Validar que la habitación exista
        $stmtHab = $pdo->prepare("SELECT * FROM habitaciones WHERE id_habitacion = :id");
        $stmtHab->bindValue(':id', intval($id_habitacion), PDO::PARAM_INT);
        $stmtHab->execute();
        $habitacion = $stmtHab->fetch(PDO::FETCH_ASSOC);
        if (!$habitacion) {
            echo json_encode(['error' => 'La habitación especificada no existe.']);
            exit;
        }

        // Validar que el cliente exista
        $stmtCli = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = :id");
        $stmtCli->bindValue(':id', intval($id_cliente), PDO::PARAM_INT);
        $stmtCli->execute();
        $cliente = $stmtCli->fetch(PDO::FETCH_ASSOC);
        if (!$cliente) {
            echo json_encode(['error' => 'El cliente especificado no existe.']);
            exit;
        }

        $data = [
            'id_cliente'     => $id_cliente,
            'id_habitacion'  => $id_habitacion,
            'fecha_entrada'  => $fecha_entrada,
            'fecha_salida'   => $fecha_salida,
            'estado_reserva' => $estado_reserva
        ];

        $ok = $superModel->create('reservas', $data);
        if ($ok) {
            echo json_encode(['success' => true, 'msg' => 'Reserva creada']);
        } else {
            echo json_encode(['error' => 'No se pudo crear la reserva']);
        }
        exit;
    } elseif ($method === 'PUT') {
        if (!$id) {
            echo json_encode(['error' => 'Falta id para actualizar']);
            exit;
        }
        parse_str(file_get_contents("php://input"), $input);

        // Consultar la reserva actual para validar cambios de estado
        $reservaActual = $superModel->getById('reservas', $id);
        if (!$reservaActual) {
            echo json_encode(['error' => 'Reserva no encontrada']);
            exit;
        }
        $estadoActual = $reservaActual['estado_reserva'];
        $estadoNuevo = $input['estado_reserva'] ?? $estadoActual;

        $errorTransicion = validarTransicionEstado($estadoActual, $estadoNuevo);
        if ($errorTransicion) {
            echo json_encode(['error' => $errorTransicion]);
            exit;
        }

        // Mezclar datos antiguos y nuevos
        $dataUpdate = $reservaActual;
        foreach ($input as $k => $v) {
            $dataUpdate[$k] = $v;
        }
        unset($dataUpdate['id_reserva']);

        $ok = $superModel->update('reservas', $id, $dataUpdate);
        echo json_encode($ok
            ? ['success' => true, 'msg' => 'Reserva actualizada']
            : ['error' => 'No se pudo actualizar la reserva']);
        exit;
    } elseif ($method === 'DELETE') {
        if (!$id) {
            echo json_encode(['error' => 'Falta id para eliminar']);
            exit;
        }
        $reservaActual = $superModel->getById('reservas', $id);
        if (!$reservaActual) {
            echo json_encode(['error' => 'No existe la reserva']);
            exit;
        }
        // No se permite eliminar si ya hay check-in o check-out
        if (in_array($reservaActual['estado_reserva'], ['CheckIn', 'CheckOut'])) {
            echo json_encode(['error' => 'No se puede eliminar una reserva con CheckIn/CheckOut']);
            exit;
        }
        $ok = $superModel->delete('reservas', $id);
        echo json_encode($ok
            ? ['success' => true, 'msg' => 'Reserva eliminada']
            : ['error' => 'No se pudo eliminar la reserva']);
        exit;
    } else {
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
} catch (Exception $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
    exit;
}

function validarTransicionEstado($actual, $nuevo)
{
    switch ($actual) {
        case 'Pendiente':
            if (!in_array($nuevo, ['Pendiente', 'Confirmada', 'Cancelada'])) {
                return "No se puede pasar de $actual a $nuevo";
            }
            break;
        case 'Confirmada':
            if (!in_array($nuevo, ['Confirmada', 'CheckIn', 'Cancelada'])) {
                return "No se puede pasar de $actual a $nuevo";
            }
            break;
        case 'CheckIn':
            if (!in_array($nuevo, ['CheckIn', 'CheckOut'])) {
                return "No se puede pasar de $actual a $nuevo";
            }
            break;
        case 'CheckOut':
            if ($nuevo !== 'CheckOut') {
                return "Una reserva en CheckOut no puede cambiarse a $nuevo";
            }
            break;
        case 'Cancelada':
            if ($nuevo !== 'Cancelada') {
                return "No se puede cambiar el estado de una reserva Cancelada a $nuevo";
            }
            break;
        default:
            return "Estado actual desconocido: $actual";
    }
    return null;
}
