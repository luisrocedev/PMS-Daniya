<?php
// public/empleados.php

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
    <title>Gestión de Empleados - PMS Daniya Denia</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Gestión de Empleados</h2>

            <!-- FILTROS / BÚSQUEDA -->
            <div class="card">
                <h3>Buscar Empleados</h3>
                <form onsubmit="event.preventDefault(); listarEmpleadosPaginado(1);">
                    <label for="buscarTxt">Texto (Nombre, Apellidos, DNI):</label>
                    <input type="text" id="buscarTxt" placeholder="Ej: 'López'">

                    <label for="buscarRol">Rol:</label>
                    <select id="buscarRol">
                        <option value="">Todos</option>
                        <!-- Se llenará dinámicamente en cargarRolesYDeps(), pero dejamos esta opción por defecto -->
                    </select>

                    <label for="buscarDep">Departamento:</label>
                    <select id="buscarDep">
                        <option value="">Todos</option>
                        <!-- Igualmente se llenará dinámicamente -->
                    </select>

                    <button type="submit" class="btn">Aplicar Filtros</button>
                </form>
            </div>

            <!-- TABLA DE EMPLEADOS -->
            <div class="card">
                <h3>Listado de Empleados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>ID Rol</th>
                            <th>ID Depto.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-empleados">
                        <!-- Se llena con JS -->
                    </tbody>
                </table>

                <!-- Contenedor para la paginación -->
                <div id="paginacionEmpleados" style="margin-top: 1rem;">
                    <!-- Aquí se renderizan botones "Anterior", "Siguiente", etc. -->
                </div>
            </div>

            <!-- FORMULARIO PARA CREAR UN NUEVO EMPLEADO -->
            <div class="card">
                <h3>Crear Nuevo Empleado</h3>
                <form onsubmit="event.preventDefault(); crearEmpleado();">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" required>

                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" required>

                    <label for="dni">DNI:</label>
                    <input type="text" id="dni" required>

                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono">

                    <label for="email">Email:</label>
                    <input type="email" id="email">

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion">

                    <label for="fecha_contrat">Fecha de Contratación:</label>
                    <input type="date" id="fecha_contrat">

                    <label for="id_rol">Rol:</label>
                    <select id="id_rol">
                        <!-- Se cargará dinámicamente con cargarRolesYDeps() -->
                    </select>

                    <label for="id_departamento">Departamento:</label>
                    <select id="id_departamento">
                        <!-- Se cargará dinámicamente -->
                    </select>

                    <button type="submit" class="btn">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Normalmente, tu main.js iría aparte; para demo, mostramos inline -->
    <script>
        // ================== DESPLEGABLES DINÁMICOS DE ROL Y DEPARTAMENTO ==================
        function cargarRolesYDeps() {
            // Cargar roles
            fetch('../api/roles.php')
                .then(res => res.json())
                .then(roles => {
                    const selRol = document.getElementById('id_rol');
                    const selRol2 = document.getElementById('buscarRol');
                    if (!selRol || !selRol2) return;

                    // Limpiamos <select> antes de rellenar
                    selRol.innerHTML = '';
                    selRol2.innerHTML = '<option value="">Todos</option>';

                    roles.forEach(r => {
                        // Para el formulario de creación
                        const opt = document.createElement('option');
                        opt.value = r.id_rol;
                        opt.textContent = r.nombre_rol;
                        selRol.appendChild(opt);

                        // Para el filtro
                        const opt2 = document.createElement('option');
                        opt2.value = r.id_rol;
                        opt2.textContent = r.nombre_rol;
                        selRol2.appendChild(opt2);
                    });
                })
                .catch(e => console.error('Error al cargar roles:', e));

            // Cargar departamentos
            fetch('../api/departamentos.php')
                .then(res => res.json())
                .then(deps => {
                    const selDep = document.getElementById('id_departamento');
                    const selDep2 = document.getElementById('buscarDep');
                    if (!selDep || !selDep2) return;

                    selDep.innerHTML = '';
                    selDep2.innerHTML = '<option value="">Todos</option>';

                    deps.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id_departamento;
                        opt.textContent = d.nombre_departamento;
                        selDep.appendChild(opt);

                        // Para el filtro
                        const opt2 = document.createElement('option');
                        opt2.value = d.id_departamento;
                        opt2.textContent = d.nombre_departamento;
                        selDep2.appendChild(opt2);
                    });
                })
                .catch(e => console.error('Error al cargar departamentos:', e));
        }

        // ================== LISTADO + FILTRO + PAGINACIÓN ==================
        let currentLimitEmp = 5; // Nº de empleados por página

        function listarEmpleadosPaginado(page = 1) {
            // Tomamos filtros
            const search = document.getElementById('buscarTxt').value || '';
            const rol = document.getElementById('buscarRol').value || '';
            const dep = document.getElementById('buscarDep').value || '';

            // Construimos la URL con parámetros
            let url = `../api/empleados.php?page=${page}&limit=${currentLimitEmp}`;

            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (rol) url += `&rol=${encodeURIComponent(rol)}`;
            if (dep) url += `&dep=${encodeURIComponent(dep)}`;

            fetch(url)
                .then(r => r.json())
                .then(obj => {
                    // obj: { data, total, page, limit }
                    const data = obj.data || [];
                    const total = obj.total || 0;
                    const page = obj.page || 1;
                    const limit = obj.limit || currentLimitEmp;

                    // Rellenar tabla
                    const tbody = document.getElementById('tabla-empleados');
                    if (!tbody) return;
                    tbody.innerHTML = '';

                    data.forEach(emp => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
              <td>${emp.id_empleado}</td>
              <td>${emp.nombre}</td>
              <td>${emp.apellidos}</td>
              <td>${emp.dni}</td>
              <td>${emp.telefono || ''}</td>
              <td>${emp.email || ''}</td>
              <td>${emp.id_rol}</td>
              <td>${emp.id_departamento}</td>
              <td>
                <button class="btn" onclick="eliminarEmpleado(${emp.id_empleado})">Eliminar</button>
              </td>
            `;
                        tbody.appendChild(tr);
                    });

                    // Renderizar controles de paginación
                    renderPaginacionEmpleados(page, limit, total);
                })
                .catch(err => console.error(err));
        }

        function renderPaginacionEmpleados(page, limit, total) {
            const divPag = document.getElementById('paginacionEmpleados');
            if (!divPag) return;

            divPag.innerHTML = ''; // limpiar
            const totalPages = Math.ceil(total / limit);

            // Botón Anterior
            if (page > 1) {
                const btnPrev = document.createElement('button');
                btnPrev.classList.add('btn');
                btnPrev.textContent = 'Anterior';
                btnPrev.onclick = () => listarEmpleadosPaginado(page - 1);
                divPag.appendChild(btnPrev);
            }

            // Info
            const info = document.createElement('span');
            info.style.margin = '0 10px';
            info.textContent = `Página ${page} de ${totalPages} - Total: ${total}`;
            divPag.appendChild(info);

            // Botón Siguiente
            if (page < totalPages) {
                const btnNext = document.createElement('button');
                btnNext.classList.add('btn');
                btnNext.textContent = 'Siguiente';
                btnNext.onclick = () => listarEmpleadosPaginado(page + 1);
                divPag.appendChild(btnNext);
            }
        }

        // ================== CREAR EMPLEADO ==================
        function crearEmpleado() {
            const nombre = document.getElementById('nombre').value;
            const apellidos = document.getElementById('apellidos').value;
            const dni = document.getElementById('dni').value;
            const telefono = document.getElementById('telefono').value;
            const email = document.getElementById('email').value;
            const direccion = document.getElementById('direccion').value;
            const fecha_contrat = document.getElementById('fecha_contrat').value;
            const id_rol = document.getElementById('id_rol').value;
            const id_departamento = document.getElementById('id_departamento').value;

            fetch('../api/empleados.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        nombre,
                        apellidos,
                        dni,
                        telefono,
                        email,
                        direccion,
                        fecha_contrat,
                        id_rol,
                        id_departamento
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.msg);
                        // Volvemos a la primera página
                        listarEmpleadosPaginado(1);
                        // Limpiamos los campos
                        document.getElementById('nombre').value = '';
                        document.getElementById('apellidos').value = '';
                        document.getElementById('dni').value = '';
                        document.getElementById('telefono').value = '';
                        document.getElementById('email').value = '';
                        document.getElementById('direccion').value = '';
                        document.getElementById('fecha_contrat').value = '';
                    } else {
                        alert(data.error || 'No se pudo crear el empleado');
                    }
                })
                .catch(err => console.error(err));
        }

        // ================== ELIMINAR EMPLEADO ==================
        function eliminarEmpleado(idEmp) {
            if (!confirm('¿Seguro que deseas eliminar este empleado?')) return;

            fetch(`../api/empleados.php?id=${idEmp}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.msg);
                        // Mantenerse en la misma página
                        listarEmpleadosPaginado();
                    } else {
                        alert(data.error || 'No se pudo eliminar el empleado');
                    }
                })
                .catch(err => console.error(err));
        }

        // ================== EVENTO DE CARGA DE LA PÁGINA ==================
        document.addEventListener('DOMContentLoaded', () => {
            cargarRolesYDeps(); // Carga desplegables de roles y departamentos
            listarEmpleadosPaginado(1); // Muestra la primera página
        });
    </script>

</body>

</html>