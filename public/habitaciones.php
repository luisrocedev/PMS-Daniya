<?php
// Configuración de la página
$pageTitle = 'Gestión de Habitaciones - PMS Daniya Denia';

// Contenido específico de la página
ob_start();
?>

<h2 class="page-title">Gestión de Habitaciones</h2>

<!-- FILTROS -->
<div class="card">
    <h3>Buscar Habitaciones</h3>
    <form onsubmit="event.preventDefault(); listarHabitacionesPaginado(1);">
        <label for="searchHab">Número/Tipo:</label>
        <input type="text" id="searchHab">

        <label for="estadoHab">Estado:</label>
        <select id="estadoHab">
            <option value="">Todos</option>
            <option value="Disponible">Disponible</option>
            <option value="Ocupada">Ocupada</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select>

        <button type="submit" class="btn">Filtrar</button>
    </form>
</div>

<!-- TABLA -->
<div class="card">
    <h3>Listado de Habitaciones</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Número</th>
                <th>Tipo</th>
                <th>Capacidad</th>
                <th>Piso</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tabla-habitaciones">
            <!-- Llenado con JS -->
        </tbody>
    </table>
    <div id="paginacionHabs" style="margin-top:1rem;"></div>
</div>

<!-- FORM CREACIÓN -->
<div class="card">
    <h3>Nueva Habitación</h3>
    <form onsubmit="event.preventDefault(); crearHabitacion();">
        <label for="numHab">Número:</label>
        <input type="text" id="numHab" required>

        <label for="tipoHab">Tipo:</label>
        <input type="text" id="tipoHab" required>

        <label for="capHab">Capacidad:</label>
        <input type="number" id="capHab" required>

        <label for="pisoHab">Piso:</label>
        <input type="number" id="pisoHab" required>

        <label for="estHab">Estado:</label>
        <select id="estHab">
            <option value="Disponible">Disponible</option>
            <option value="Ocupada">Ocupada</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select>

        <button type="submit" class="btn">Crear</button>
    </form>
</div>

<?php
$pageContent = ob_get_clean();

// Scripts específicos de la página
$extraScripts = '
<script src="js/habitaciones.js"></script>
<script src="js/navigation.js"></script>
';

// Incluir el template
include __DIR__ . '/../partials/template.php';
?>