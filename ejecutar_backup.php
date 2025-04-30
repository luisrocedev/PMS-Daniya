<?php
// Script para ejecutar backup.sh cada 3 días desde PHP
$backupDir = __DIR__ . '/backups';
$script = __DIR__ . '/backup.sh';

// Buscar el último backup
$archivos = glob($backupDir . '/pms_daniya_backup_*.sql');
$ejecutar = true;
if ($archivos) {
    rsort($archivos);
    $ultimo = $archivos[0];
    $fechaUltimo = filemtime($ultimo);
    $dias = floor((time() - $fechaUltimo) / 86400);
    if ($dias < 3) {
        echo "La última copia de seguridad fue hace $dias días. No es necesario crear una nueva.";
        $ejecutar = false;
    }
}

if ($ejecutar) {
    // Dar permisos de ejecución si es necesario
    if (!is_executable($script)) {
        chmod($script, 0755);
    }
    // Ejecutar el script y mostrar salida
    $output = [];
    $return = 0;
    exec("$script 2>&1", $output, $return);
    echo "<pre>" . implode("\n", $output) . "</pre>";
    if ($return === 0) {
        echo "Backup realizado correctamente.";
    } else {
        echo "Error al ejecutar el backup.";
    }
}
