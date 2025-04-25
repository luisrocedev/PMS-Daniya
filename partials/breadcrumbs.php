<?php
// partials/breadcrumbs.php

function getBreadcrumbs()
{
    $currentPage = basename($_SERVER['PHP_SELF']);
    $pageTitles = [
        'dashboard.php' => 'Inicio',
        'checkin_checkout.php' => 'Check-in/Check-out',
        'checkins_hoy.php' => 'Check-in de hoy',
        'reportes.php' => 'Reportes',
        'clientes.php' => 'Clientes',
        'asistente_cliente.php' => 'Asistente Virtual',
        'empleados.php' => 'Empleados',
        'habitaciones.php' => 'Habitaciones',
        'reservas.php' => 'Reservas',
        'reportes_avanzados.php' => 'Reportes Avanzados',
        'mantenimiento.php' => 'Mantenimiento',
        'facturas.php' => 'Facturas',
        'tarifas.php' => 'Tarifas',
        'calendario.php' => 'Calendario',
        'marketing_emails.php' => 'Emails de Marketing',
        'rrhh.php' => 'RRHH',
    ];

    $breadcrumbs = [];
    $breadcrumbs[] = '<div class="breadcrumb-item"><a href="dashboard.php">Inicio</a></div>';

    if ($currentPage !== 'dashboard.php') {
        $pageTitle = $pageTitles[$currentPage] ?? ucfirst(str_replace(['-', '.php'], [' ', ''], $currentPage));
        $breadcrumbs[] = '<div class="breadcrumb-item active">' . htmlspecialchars($pageTitle) . '</div>';
    }

    return '<div class="breadcrumb">' . implode('', $breadcrumbs) . '</div>';
}
