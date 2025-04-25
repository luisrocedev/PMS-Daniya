<?php
// partials/navbar.php
$navItems = [
    'dashboard.php'        => 'Inicio',
    'checkin_checkout.php' => 'Check-in/Check-out',
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
    <div class="nav-links d-flex align-items-center">
        <?php foreach ($navItems as $file => $label): ?>
            <a href="<?php echo $file; ?>"><?php echo $label; ?></a>
        <?php endforeach; ?>
        <button class="theme-toggle ms-3" id="theme-toggle" title="Cambiar tema">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</div>