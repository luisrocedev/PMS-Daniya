<?php
// api/checkin_uploads.php
header('Content-Type: application/json');
session_start();

// Comprobamos que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

// Se espera recibir por POST un id_reserva, un campo "dni" y un campo "firma"
$id_reserva = $_POST['id_reserva'] ?? 0;
$dni = trim($_POST['dni'] ?? '');
$firma = trim($_POST['firma'] ?? '');

if (!$id_reserva) {
    echo json_encode(['error' => 'Falta id_reserva']);
    exit;
}
if (empty($dni)) {
    echo json_encode(['error' => 'Falta el número de documento']);
    exit;
}
if (empty($firma)) {
    echo json_encode(['error' => 'Falta la firma']);
    exit;
}

// En este contexto, en lugar de almacenar rutas de archivos (ya que no se adjuntan imágenes),
// vamos a almacenar directamente los datos de texto en los campos 'documento_url' y 'firma_url'.
// (Si lo prefieres, podrías renombrar las columnas en la tabla a "documento" y "firma").
$documentoTexto = $dni;
$firmaTexto = $firma;

// Insertamos la información de check-in en la tabla 'checkin_info'
// (Asegúrate de tener esta tabla creada. Un ejemplo de tabla podría ser:)
//
// CREATE TABLE checkin_info (
//     id_checkin INT AUTO_INCREMENT PRIMARY KEY,
//     id_reserva INT NOT NULL,
//     documento_url VARCHAR(255),    -- Aquí se guardará el número de documento
//     firma_url VARCHAR(255),        -- Aquí se guardará la firma (texto)
//     fecha_checkin DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva)
// );
$sql = "INSERT INTO checkin_info (id_reserva, documento_url, firma_url, fecha_checkin)
        VALUES (:id_reserva, :doc, :firma, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_reserva', $id_reserva);
$stmt->bindValue(':doc', $documentoTexto);
$stmt->bindValue(':firma', $firmaTexto);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'msg' => 'Datos de check-in guardados correctamente',
        'documento' => $documentoTexto,
        'firma' => $firmaTexto
    ]);
} else {
    echo json_encode(['error' => 'No se pudo insertar en la base de datos']);
}
