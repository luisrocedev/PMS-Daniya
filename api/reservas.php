<?php
// api/reservas.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$superModel = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? 0;

/**
 * Estados: 
 *  - Pendiente
 *  - Confirmada
 *  - Cancelada
 *  - CheckIn
 *  - CheckOut
 * 
 * Lógica de transiciones (ejemplo):
 *  - Pendiente → Confirmada
 *  - Confirmada → Cancelada (si no llegó el día o se decidió anular)
 *  - Confirmada → CheckIn (en la fecha de llegada)
 *  - CheckIn → CheckOut (al salir)
 *  - No puedes hacer CheckIn si no está Confirmada
 *  - No puedes hacer CheckOut si no está en CheckIn
 *  - No puedes cancelar si ya estás en CheckIn o CheckOut
 */

if ($method === 'GET') {
    // Listar o una sola
    if ($id) {
        $reserva = $superModel->getById('reservas', $id);
        echo json_encode($reserva);
    } else {
        $all = $superModel->getAll('reservas');
        echo json_encode($all);
    }
    exit;
} elseif ($method === 'POST') {
    // Crear reserva
    $id_cliente    = $_POST['id_cliente']    ?? '';
    $id_habitacion = $_POST['id_habitacion'] ?? '';
    $fecha_entrada = $_POST['fecha_entrada'] ?? '';
    $fecha_salida  = $_POST['fecha_salida']  ?? '';
    // Si se crea desde un formulario y ya quieres establecerla en “Confirmada” está OK,
    // o la inicias en “Pendiente”. Decisión tuya.
    $estado_reserva = $_POST['estado_reserva'] ?? 'Pendiente';

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
    // Actualizar
    if (!$id) {
        echo json_encode(['error' => 'Falta id para actualizar']);
        exit;
    }

    // Obtenemos datos del body (PUT no usa $_POST)
    parse_str(file_get_contents("php://input"), $input);
    // Ejemplo: $input = [ 'estado_reserva' => 'Cancelada', 'fecha_salida' => '2025-12-31', ...]

    // Antes de actualizar, vemos el estado actual
    $reservaActual = $superModel->getById('reservas', $id);
    if (!$reservaActual) {
        echo json_encode(['error' => 'Reserva no encontrada']);
        exit;
    }

    $estadoActual = $reservaActual['estado_reserva'];
    $estadoNuevo  = $input['estado_reserva'] ?? $estadoActual; // si no viene, se queda igual

    // Validar la transición
    $errorTransicion = validarTransicionEstado($estadoActual, $estadoNuevo);
    if ($errorTransicion) {
        echo json_encode(['error' => $errorTransicion]);
        exit;
    }

    // Si pasa la validación, hacemos el update
    // OJO: asegúrate de no pisar el estado si no mandaste 'estado_reserva'
    // Podrías mezclar la info:
    $dataUpdate = $reservaActual;  // old data
    // Acá mezclamos old data con input
    foreach ($input as $k => $v) {
        $dataUpdate[$k] = $v;
    }

    // remove id_reserva from $dataUpdate (if it exists) to avoid conflict
    unset($dataUpdate['id_reserva']);

    // actualizamos
    $ok = $superModel->update('reservas', $id, $dataUpdate);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Reserva actualizada']);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar la reserva']);
    }
    exit;
} elseif ($method === 'DELETE') {
    // Eliminar
    if (!$id) {
        echo json_encode(['error' => 'Falta id para eliminar']);
        exit;
    }
    // Podrías exigir que solo se pueda eliminar si la reserva no ha tenido CheckIn.
    // O si su estado es Pendiente/Confirmada. 
    // Ejemplo:
    $reservaActual = $superModel->getById('reservas', $id);
    if (!$reservaActual) {
        echo json_encode(['error' => 'No existe la reserva']);
        exit;
    }
    if (in_array($reservaActual['estado_reserva'], ['CheckIn', 'CheckOut'])) {
        echo json_encode(['error' => 'No se puede eliminar una reserva con CheckIn/CheckOut']);
        exit;
    }

    $ok = $superModel->delete('reservas', $id);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Reserva eliminada']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar la reserva']);
    }
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

/**
 * Función para validar transiciones de estado.
 * Retorna null si es válido, o un mensaje de error si no.
 */
function validarTransicionEstado($actual, $nuevo)
{
    // Mapeamos las transiciones válidas
    // Podrías usar un array de arrays o un switch
    switch ($actual) {
        case 'Pendiente':
            // Pendiente -> Confirmada, Cancelada
            // (podrías permitir Cancela? Queda a tu criterio)
            if (!in_array($nuevo, ['Pendiente', 'Confirmada', 'Cancelada'])) {
                return "No se puede pasar de $actual a $nuevo";
            }
            break;

        case 'Confirmada':
            // Confirmada -> CheckIn, Cancelada, (o permanecer Confirmada)
            if (!in_array($nuevo, ['Confirmada', 'CheckIn', 'Cancelada'])) {
                return "No se puede pasar de $actual a $nuevo";
            }
            break;

        case 'CheckIn':
            // CheckIn -> CheckOut (o seguir en CheckIn)
            if (!in_array($nuevo, ['CheckIn', 'CheckOut'])) {
                return "No se puede pasar de $actual a $nuevo";
            }
            break;

        case 'CheckOut':
            // CheckOut -> (no debería cambiarse a nada más)
            if ($nuevo !== 'CheckOut') {
                return "Una reserva en CheckOut no puede cambiarse a $nuevo";
            }
            break;

        case 'Cancelada':
            // Cancelada -> (no se puede reactivar en este ejemplo)
            if ($nuevo !== 'Cancelada') {
                return "No se puede cambiar el estado de una reserva Cancelada a $nuevo";
            }
            break;

        default:
            // Si llega un estado desconocido, error
            return "Estado actual desconocido: $actual";
    }
    // si todo OK
    return null;
}
