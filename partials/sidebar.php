<?php
// partials/sidebar.php

$sidebarItems = [
    'dashboard.php'           => ['Inicio', 'fas fa-home'],
    'checkin_checkout.php'    => ['Check-in/Check-out', 'fas fa-key'],
    'checkins_hoy.php'        => ['Check-in de hoy', 'fas fa-calendar-check'],
    'reportes.php'            => ['Reportes', 'fas fa-chart-bar'],
    'clientes.php'            => ['Clientes', 'fas fa-users'],
    'asistente_cliente.php'   => ['Asistente Virtual', 'fas fa-robot'],
    'empleados.php'           => ['Empleados', 'fas fa-user-tie'],
    'habitaciones.php'        => ['Habitaciones', 'fas fa-bed'],
    'reservas.php'            => ['Reservas', 'fas fa-calendar-alt'],
    'reportes_avanzados.php'  => ['Reportes Avanzados', 'fas fa-chart-line'],
    'mantenimiento.php'       => ['Mantenimiento', 'fas fa-tools'],
    'facturas.php'           => ['Facturas', 'fas fa-file-invoice-dollar'],
    'tarifas.php'            => ['Tarifas', 'fas fa-tag'],
    'calendario.php'          => ['Calendario', 'fas fa-calendar'],
    'marketing_emails.php'    => ['Emails de Marketing', 'fas fa-envelope'],
    'rrhh.php'               => ['RRHH', 'fas fa-users-cog'],
];

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<button class="sidebar-toggle d-md-none">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar">
    <?php foreach ($sidebarItems as $file => $info): ?>
        <a href="<?php echo $file; ?>" class="<?php echo $file === $currentPage ? 'active' : ''; ?>">
            <i class="<?php echo $info[1]; ?>"></i>
            <span><?php echo $info[0]; ?></span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Script de navegación -->
<script src="js/navigation.js"></script>