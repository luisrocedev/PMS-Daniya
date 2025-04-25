<?php
// api/facturas.php
header('Content-Type: application/json');
session_start();

try {
    require_once __DIR__ . '/../core/Database.php';
    require_once __DIR__ . '/../core/SuperModel.php';

    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }

    $superModel = new SuperModel();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $stats = isset($_GET['stats']) && $_GET['stats'] === 'true';

    /**
     * GET => /facturas.php
     *   - Parámetros: 
     *     - reserva: filtrar por id_reserva
     *     - fecha_inicio: filtrar desde fecha
     *     - fecha_fin: filtrar hasta fecha
     *     - page: página actual
     *     - limit: resultados por página
     *     - stats: true para obtener estadísticas
     * POST => Crear factura
     * PUT => Actualizar ?id=XX
     * DELETE => Eliminar ?id=XX
     */

    if ($method === 'GET') {
        if ($stats) {
            // Obtener estadísticas para el dashboard
            $pdo = Database::getInstance()->getConnection();

            $sql = "SELECT 
                COUNT(*) as total,
                SUM(total) as importe_total,
                AVG(total) as promedio,
                SUM(CASE WHEN metodo_pago = 'Pendiente' THEN 1 ELSE 0 END) as pendientes
                FROM facturas";

            $stmt = $pdo->query($sql);
            $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'total' => (int)$estadisticas['total'],
                'importe_total' => (float)$estadisticas['importe_total'],
                'promedio' => (float)$estadisticas['promedio'],
                'pendientes' => (int)$estadisticas['pendientes']
            ]);
            exit;
        }

        $reserva = $_GET['reserva'] ?? '';
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_GET['fecha_fin'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        $offset = ($page - 1) * $limit;

        if ($id) {
            $factura = $superModel->getById('facturas', $id);
            if ($factura) {
                // Obtener detalles adicionales si existen
                $reserva = $superModel->getById('reservas', $factura['id_reserva']);
                if ($reserva) {
                    $factura['cliente'] = $superModel->getById('clientes', $reserva['id_cliente']);
                }
            }
            echo json_encode($factura);
            exit;
        }

        $where = "WHERE 1=1";
        $params = [];

        if ($reserva) {
            $where .= " AND id_reserva = :reserva";
            $params[':reserva'] = $reserva;
        }
        if ($fecha_inicio) {
            $where .= " AND fecha_emision >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        if ($fecha_fin) {
            $where .= " AND fecha_emision <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }

        $pdo = Database::getInstance()->getConnection();

        // Consulta para contar total
        $sqlCount = "SELECT COUNT(*) as total FROM facturas $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value);
        }
        $stmtCount->execute();
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Consulta principal
        $sql = "SELECT f.*, 
                r.id_cliente,
                CONCAT(c.nombre, ' ', c.apellidos) as nombre_cliente
                FROM facturas f
                LEFT JOIN reservas r ON f.id_reserva = r.id_reserva
                LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
                $where 
                ORDER BY f.fecha_emision DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $data,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit
        ]);
        exit;
    } elseif ($method === 'POST') {
        $id_reserva = $_POST['id_reserva'] ?? 0;
        $fecha_emision = $_POST['fecha_emision'] ?? date('Y-m-d');
        $total = $_POST['total'] ?? 0;
        $metodo_pago = $_POST['metodo_pago'] ?? 'Efectivo';

        // Validaciones
        if (!$id_reserva) {
            echo json_encode(['error' => 'El ID de reserva es requerido']);
            exit;
        }

        // Verificar que la reserva existe
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM reservas WHERE id_reserva = ?");
        $stmt->execute([$id_reserva]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'La reserva especificada no existe']);
            exit;
        }

        // Verificar si ya existe una factura para esta reserva
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM facturas WHERE id_reserva = ?");
        $stmt->execute([$id_reserva]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'Ya existe una factura para esta reserva']);
            exit;
        }

        // Crear la factura
        $ok = $superModel->create('facturas', [
            'id_reserva' => $id_reserva,
            'fecha_emision' => $fecha_emision,
            'total' => $total,
            'metodo_pago' => $metodo_pago
        ]);

        if ($ok) {
            // Actualizar estado de la reserva si es necesario
            $pdo->prepare("UPDATE reservas SET estado = 'Facturada' WHERE id_reserva = ?")->execute([$id_reserva]);
        }

        echo json_encode(
            $ok
                ? ['success' => true, 'msg' => 'Factura creada con éxito']
                : ['error' => 'No se pudo crear la factura']
        );
        exit;
    } elseif ($method === 'PUT') {
        if (!$id) {
            echo json_encode(['error' => 'ID de factura requerido']);
            exit;
        }

        parse_str(file_get_contents('php://input'), $input);

        // Validar datos mínimos
        if (!isset($input['total']) || $input['total'] <= 0) {
            echo json_encode(['error' => 'El total debe ser mayor que 0']);
            exit;
        }

        $ok = $superModel->update('facturas', $id, $input);
        echo json_encode(
            $ok
                ? ['success' => true, 'msg' => 'Factura actualizada con éxito']
                : ['error' => 'No se pudo actualizar la factura']
        );
        exit;
    } elseif ($method === 'DELETE') {
        if (!$id) {
            echo json_encode(['error' => 'ID de factura requerido']);
            exit;
        }

        // Verificar si se puede eliminar
        $factura = $superModel->getById('facturas', $id);
        if (!$factura) {
            echo json_encode(['error' => 'Factura no encontrada']);
            exit;
        }

        $ok = $superModel->delete('facturas', $id);
        if ($ok) {
            // Actualizar estado de la reserva si es necesario
            $pdo = Database::getInstance()->getConnection();
            $pdo->prepare("UPDATE reservas SET estado = 'Completada' WHERE id_reserva = ?")->execute([$factura['id_reserva']]);
        }

        echo json_encode(
            $ok
                ? ['success' => true, 'msg' => 'Factura eliminada con éxito']
                : ['error' => 'No se pudo eliminar la factura']
        );
        exit;
    }
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    exit;
}
