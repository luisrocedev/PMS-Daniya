<?php
// api/facturas.php
header('Content-Type: application/json');
session_start();

try {
    require_once __DIR__ . '/../core/Database.php';
    require_once __DIR__ . '/../core/SuperModel.php';

    // Verificar autenticación
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }

    $superModel = new SuperModel();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /**
     * GET => /facturas.php
     *   - Parámetros opcionales: reserva, page, limit
     * POST => Crear factura
     * PUT  => Actualizar ?id=XX
     * DELETE => Eliminar ?id=XX
     */

    if ($method === 'GET') {
        $reserva = $_GET['reserva'] ?? '';
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit   = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 5;
        $offset = ($page - 1) * $limit;

        if ($id) {
            $fact = $superModel->getById('facturas', $id);
            echo json_encode($fact);
            exit;
        } else {
            $where = " WHERE 1=1 ";
            $params = [];

            if ($reserva) {
                $where .= " AND id_reserva = :r ";
                $params[':r'] = $reserva;
            }

            $pdo = Database::getInstance()->getConnection();

            // Consulta para contar el total
            $sqlC = "SELECT COUNT(*) as total FROM facturas $where";
            $stmtC = $pdo->prepare($sqlC);
            foreach ($params as $k => $v) {
                $stmtC->bindValue($k, $v);
            }
            $stmtC->execute();
            $total = $stmtC->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Consulta para obtener los datos
            $sql = "SELECT * FROM facturas $where LIMIT :lim OFFSET :off";
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
        // Recoger datos para crear factura
        $id_reserva    = $_POST['id_reserva'] ?? 0;
        $fecha_emision = $_POST['fecha_emision'] ?? '';
        $total         = $_POST['total'] ?? 0;
        $metodo_pago   = $_POST['metodo_pago'] ?? 'Efectivo';

        // Verificar que el id_reserva exista en la tabla reservas
        $pdo = Database::getInstance()->getConnection();
        $sqlReserva = "SELECT * FROM reservas WHERE id_reserva = :id";
        $stmtReserva = $pdo->prepare($sqlReserva);
        $stmtReserva->bindValue(':id', $id_reserva, PDO::PARAM_INT);
        $stmtReserva->execute();
        $reservaExistente = $stmtReserva->fetch(PDO::FETCH_ASSOC);

        if (!$reservaExistente) {
            echo json_encode(['error' => 'El id_reserva proporcionado no existe.']);
            exit;
        }

        // Procedemos a crear la factura
        $ok = $superModel->create('facturas', [
            'id_reserva'    => $id_reserva,
            'fecha_emision' => $fecha_emision,
            'total'         => $total,
            'metodo_pago'   => $metodo_pago
        ]);
        echo json_encode($ok
            ? ['success' => true, 'msg' => 'Factura creada']
            : ['error'   => 'No se pudo crear']);
        exit;
    }
} catch (Exception $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
    exit;
}
