<?php
// partials/sidebar.php

$sidebarItems = [
    'dashboard.php'           => 'Inicio',
    'checkin_checkout.php'    => 'Check-in/Check-out',
    'checkins_hoy.php'        => 'Check-in de hoy',        // NUEVO Ítem
    // Se reemplaza "ocupacion.php" por la nueva "ocupacion_detallada.php",
    'reportes.php'            => 'Reportes',
    'clientes.php'            => 'Clientes',
    'empleados.php'           => 'Empleados',
    'habitaciones.php'        => 'Habitaciones',
    'reservas.php'            => 'Reservas',
    'reportes_avanzados.php'  => 'Reportes Avanzados',
    'mantenimiento.php'       => 'Mantenimiento',
    'facturas.php'            => 'Facturas',
    'tarifas.php'             => 'Tarifas',
    'calendario.php'          => 'Calendario',
    'marketing_emails.php'    => 'Emails de Marketing',
    'rrhh.php'                => 'RRHH',
];

?>

<div class="sidebar">
    <?php foreach ($sidebarItems as $file => $label): ?>
        <a href="<?php echo $file; ?>"><?php echo $label; ?></a>
    <?php endforeach; ?>
</div>