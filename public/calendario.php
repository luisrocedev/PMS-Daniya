<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Calendario de Reservas</title>
    <link rel="stylesheet" href="css/style.css">

    <!-- 1) Incluir FullCalendar (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Calendario de Reservas</h2>

            <!-- Contenedor donde irá el calendario -->
            <div id="calendar"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            // Creamos la instancia del calendario
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // Vista mensual
                locale: 'es', // Para que los textos (días, meses) salgan en español

                // 2) Cargamos los eventos desde una URL (ver siguiente paso)
                events: '../api/reservas_calendar.php',

                // Opcional: puedes configurar cabecera, altura, etc.
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                }
            });

            // Renderizamos
            calendar.render();
        });
    </script>
</body>

</html>