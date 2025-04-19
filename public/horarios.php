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
    <meta charset="utf-8">
    <title>Horarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        <div class="main-content container">
            <h2 class="page-title">Horarios de Empleados</h2>

            <!-- Filtro de empleado -->
            <div class="mb-3">
                <label>Empleado (ID): </label>
                <input type="number" id="empFiltro" value="">
                <button class="btn btn-secondary" onclick="refrescar()">Aplicar</button>
            </div>

            <!-- Calendario -->
            <div id="cal" style="max-width:1000px;"></div>
        </div>
    </div>

    <script>
        let calendar;
        document.addEventListener('DOMContentLoaded', () => {
            calendar = new FullCalendar.Calendar(document.getElementById('cal'), {
                initialView: 'timeGridWeek',
                locale: 'es',
                allDaySlot: false,
                height: 'auto',
                events: obtenerEventos,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }
            });
            calendar.render();
        });

        function obtenerEventos(fetchInfo, success) {
            const emp = empFiltro.value;
            const params = new URLSearchParams({
                desde: fetchInfo.startStr.substring(0, 10),
                hasta: fetchInfo.endStr.substring(0, 10),
                ...(emp ? {
                    emp
                } : {})
            });
            fetch(`../api/horarios.php?${params}`)
                .then(r => r.json())
                .then(rows => {
                    const evs = rows.map(h => {
                        return {
                            title: `Emp ${h.id_empleado} (Turno ${h.id_turno})`,
                            start: h.fecha_inicio,
                            end: new Date(new Date(h.fecha_fin).getTime() + 86400000).toISOString().substring(0, 10), // +1 día
                            allDay: true,
                            backgroundColor: '#0d6efd'
                        };
                    });
                    success(evs);
                });
        }

        function refrescar() {
            calendar.refetchEvents();
        }
    </script>
</body>

</html>