<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';
$pdo   = Database::getInstance()->getConnection();
$super = new SuperModel();

try {
    $m     = $_SERVER['REQUEST_METHOD'];
    $id    = $_GET['id'] ?? 0;

    if ($m === 'GET') {
        $where = " WHERE 1=1 ";
        $p = [];
        if (!empty($_GET['emp'])) {
            $where .= " AND id_empleado=:e";
            $p[':e'] = $_GET['emp'];
        }
        if (!empty($_GET['desde'])) {
            $where .= " AND fecha>=:d";
            $p[':d'] = $_GET['desde'];
        }
        if (!empty($_GET['hasta'])) {
            $where .= " AND fecha<=:h";
            $p[':h'] = $_GET['hasta'];
        }
        if ($id) {
            echo json_encode($super->getById('asistencia', $id));
            exit;
        }
        $sql = "SELECT a.*, e.nombre, e.apellidos FROM asistencia a LEFT JOIN empleados e ON a.id_empleado = e.id_empleado $where ORDER BY a.fecha DESC";
        $st = $pdo->prepare($sql);
        foreach ($p as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        echo json_encode($st->fetchAll(PDO::FETCH_ASSOC));
        exit;
    } elseif ($m === 'POST') {
        // Fichaje realista: entrada/salida
        $id_emp = $_POST['id_empleado'] ?? 0;
        $tipo   = $_POST['tipo'] ?? 'entrada'; // entrada|salida
        if (!$id_emp) {
            echo json_encode(['error' => 'Falta id_empleado']);
            exit;
        }
        $fecha = date('Y-m-d');
        // Buscar registro de hoy
        $sqlSel = "SELECT * FROM asistencia WHERE id_empleado = :e AND fecha = :f";
        $stSel = $pdo->prepare($sqlSel);
        $stSel->execute([':e' => $id_emp, ':f' => $fecha]);
        $asis = $stSel->fetch(PDO::FETCH_ASSOC);

        if ($tipo === 'entrada') {
            if (!$asis) {
                // No hay registro hoy, crear con hora_entrada
                $sql = "INSERT INTO asistencia (id_empleado, fecha, hora_entrada) VALUES (:e, :f, NOW())";
                $st = $pdo->prepare($sql);
                $st->execute([':e' => $id_emp, ':f' => $fecha]);
                echo json_encode(['success' => true, 'msg' => 'Entrada registrada']);
                exit;
            } elseif (!$asis['hora_entrada']) {
                // Hay registro pero sin hora_entrada
                $sql = "UPDATE asistencia SET hora_entrada = NOW() WHERE id_asistencia = :id";
                $st = $pdo->prepare($sql);
                $st->execute([':id' => $asis['id_asistencia']]);
                echo json_encode(['success' => true, 'msg' => 'Entrada registrada']);
                exit;
            } else {
                echo json_encode(['error' => 'Ya has fichado la entrada hoy.']);
                exit;
            }
        } elseif ($tipo === 'salida') {
            if (!$asis) {
                echo json_encode(['error' => 'Primero debes fichar la entrada.']);
                exit;
            } elseif (!$asis['hora_entrada']) {
                echo json_encode(['error' => 'Primero debes fichar la entrada.']);
                exit;
            } elseif ($asis['hora_salida']) {
                echo json_encode(['error' => 'Ya has fichado la salida hoy.']);
                exit;
            } else {
                $sql = "UPDATE asistencia SET hora_salida = NOW() WHERE id_asistencia = :id";
                $st = $pdo->prepare($sql);
                $st->execute([':id' => $asis['id_asistencia']]);
                echo json_encode(['success' => true, 'msg' => 'Salida registrada']);
                exit;
            }
        } else {
            echo json_encode(['error' => 'Tipo de fichaje no válido']);
            exit;
        }
    } elseif ($m === 'PUT') {
        if (!$id) {
            echo json_encode(['error' => 'Falta id']);
            exit;
        }
        parse_str(file_get_contents('php://input'), $data);
        // Convertir campos vacíos a NULL y horas a DATETIME si corresponde
        foreach (['hora_entrada', 'hora_salida'] as $campo) {
            if (isset($data[$campo])) {
                if (trim($data[$campo]) === '') {
                    $data[$campo] = null;
                } else {
                    // Si es solo hora (formato HH:MM), convertir a DATETIME usando la fecha del registro
                    if (preg_match('/^\d{2}:\d{2}$/', $data[$campo])) {
                        // Obtener la fecha del registro
                        $sqlFecha = "SELECT fecha FROM asistencia WHERE id_asistencia = :id";
                        $stFecha = $pdo->prepare($sqlFecha);
                        $stFecha->execute([':id' => $id]);
                        $fecha = $stFecha->fetchColumn();
                        if ($fecha) {
                            $data[$campo] = $fecha . ' ' . $data[$campo] . ':00';
                        }
                    }
                }
            }
        }
        echo json_encode($super->update('asistencia', $id, $data) ? ['success' => true] : ['error' => 'No se pudo actualizar']);
    } elseif ($m === 'DELETE') {
        if (!$id) {
            echo json_encode(['error' => 'Falta id']);
            exit;
        }
        echo json_encode($super->delete('asistencia', $id) ? ['success' => true] : ['error' => 'No se pudo eliminar']);
    } else echo json_encode(['error' => 'Método no permitido']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
    exit;
}
