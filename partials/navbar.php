<?php
// partials/navbar.php

// Opcional: definimos un array con los enlaces de la barra
$navItems = [
    'dashboard.php'        => 'Inicio',
    'checkin_checkout.php' => 'Check-in/Check-out',
    'ocupacion.php'        => 'Ocupación',
    'reportes.php'         => 'Reportes',
    'clientes.php'         => 'Clientes',
    'empleados.php'        => 'Empleados',
    'habitaciones.php'     => 'Habitaciones',
    'reservas.php'         => 'Reservas',
    'mantenimiento.php'    => 'Mantenimiento',
    'facturas.php'         => 'Facturas',
    '../logout.php'        => 'Salir'
];

?>

<div class="navbar">
    <h1>Daniya Denia</h1>
    <div class="nav-links">
        <?php foreach ($navItems as $file => $label): ?>
            <a href="<?php echo $file; ?>"><?php echo $label; ?></a>
        <?php endforeach; ?>
    </div>
</div>