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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Empleados - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content p-4 w-100">
            <h2 class="page-title mb-4">Gestión de Empleados</h2>

            <div id="empleados-pages">
                <div class="content-page active" data-page="1">
                    <!-- FILTROS / BÚSQUEDA -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Buscar Empleados</h3>
                            <form onsubmit="event.preventDefault(); listarEmpleadosPaginado(1);">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="buscarTxt" class="form-label">Texto (Nombre, Apellidos, DNI):</label>
                                        <input type="text" id="buscarTxt" class="form-control" placeholder="Ej: 'López'">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="buscarRol" class="form-label">Rol:</label>
                                        <select id="buscarRol" class="form-select">
                                            <option value="">Todos</option>
                                            <!-- Se llenará dinámicamente en cargarRolesYDeps() -->
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="buscarDep" class="form-label">Departamento:</label>
                                        <select id="buscarDep" class="form-select">
                                            <option value="">Todos</option>
                                            <!-- Se llenará dinámicamente -->
                                        </select>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Aplicar Filtros
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="content-page" data-page="2">
                    <!-- TABLA DE EMPLEADOS -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Listado de Empleados</h3>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
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
                            </div>

                            <!-- Contenedor para la paginación API -->
                            <div id="paginacionEmpleados" style="margin-top: 1rem;">
                                <!-- Aquí se renderizan botones "Anterior", "Siguiente", etc. -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-page" data-page="3">
                    <!-- FORMULARIO PARA CREAR UN NUEVO EMPLEADO -->
                    <div class="card">
                        <div class="card-body p-2">
                            <h3 class="card-title mb-2 text-center">Crear Nuevo Empleado</h3>
                            <div class="row justify-content-center">
                                <div class="col-md-12">
                                    <form id="formCrearEmpleado" onsubmit="event.preventDefault(); crearEmpleado();" class="needs-validation" novalidate>
                                        <div class="row g-1">
                                            <div class="col-md-6 mb-1">
                                                <label for="nombre" class="form-label small">Nombre:</label>
                                                <input type="text" id="nombre" class="form-control form-control-sm" required>
                                            </div>

                                            <div class="col-md-6 mb-1">
                                                <label for="apellidos" class="form-label small">Apellidos:</label>
                                                <input type="text" id="apellidos" class="form-control form-control-sm" required>
                                            </div>
                                        </div>

                                        <div class="row g-1">
                                            <div class="col-md-4 mb-1">
                                                <label for="dni" class="form-label small">DNI:</label>
                                                <input type="text" id="dni" class="form-control form-control-sm" required>
                                            </div>

                                            <div class="col-md-4 mb-1">
                                                <label for="telefono" class="form-label small">Teléfono:</label>
                                                <input type="text" id="telefono" class="form-control form-control-sm">
                                            </div>

                                            <div class="col-md-4 mb-1">
                                                <label for="email" class="form-label small">Email:</label>
                                                <input type="email" id="email" class="form-control form-control-sm">
                                            </div>
                                        </div>

                                        <div class="row g-1">
                                            <div class="col-md-12 mb-1">
                                                <label for="direccion" class="form-label small">Dirección:</label>
                                                <input type="text" id="direccion" class="form-control form-control-sm">
                                            </div>
                                        </div>

                                        <div class="row g-1">
                                            <div class="col-md-4 mb-1">
                                                <label for="fecha_contrat" class="form-label small">F. Contratación:</label>
                                                <input type="date" id="fecha_contrat" class="form-control form-control-sm">
                                            </div>

                                            <div class="col-md-4 mb-1">
                                                <label for="id_rol" class="form-label small">Rol:</label>
                                                <select id="id_rol" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>Seleccione rol</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4 mb-1">
                                                <label for="id_departamento" class="form-label small">Departamento:</label>
                                                <select id="id_departamento" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>Seleccione departamento</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mt-2 d-grid">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-user-plus me-1"></i>Crear Empleado
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controles de navegación de páginas - estilo consistente con otras secciones -->
                <div class="page-nav text-center mt-4">
                    <button id="prevEmp" class="btn btn-secondary me-2">Anterior</button>
                    <span class="page-indicator">Página <span id="currentEmpPage">1</span> de <span id="totalEmpPages">3</span></span>
                    <button id="nextEmp" class="btn btn-secondary">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

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

                    if (data.length === 0) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = '<td colspan="9" class="text-center">No se encontraron empleados con los filtros seleccionados</td>';
                        tbody.appendChild(tr);
                    } else {
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
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-warning" onclick="editarEmpleado(${emp.id_empleado})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="eliminarEmpleado(${emp.id_empleado})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }

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
                btnPrev.classList.add('btn', 'btn-outline-primary', 'me-2');
                btnPrev.innerHTML = '<i class="fas fa-chevron-left me-1"></i> Anterior';
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
                btnNext.classList.add('btn', 'btn-outline-primary', 'ms-2');
                btnNext.innerHTML = 'Siguiente <i class="fas fa-chevron-right ms-1"></i>';
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

        // ================== EDICIÓN DE EMPLEADO ==================
        function editarEmpleado(idEmp) {
            alert('Función de edición en desarrollo');
            // Esta función se implementaría usando un modal similar al de clientes
        }

        // ================== EVENTO DE CARGA DE LA PÁGINA ==================
        document.addEventListener('DOMContentLoaded', () => {
            cargarRolesYDeps(); // Carga desplegables de roles y departamentos
            listarEmpleadosPaginado(1); // Muestra la primera página
            initializeEmpleadosPageNav(); // Inicializar paginación interna
        });

        // ================== PAGINACIÓN INTERNA ==================
        function initializeEmpleadosPageNav() {
            const pages = document.querySelectorAll('#empleados-pages .content-page');
            const prevBtn = document.getElementById('prevEmp');
            const nextBtn = document.getElementById('nextEmp');
            const currentPageEl = document.getElementById('currentEmpPage');
            const totalPagesEl = document.getElementById('totalEmpPages');
            let current = 0;

            // Establecer el total de páginas
            if (totalPagesEl) totalPagesEl.textContent = pages.length;

            function updateButtons() {
                if (prevBtn) prevBtn.disabled = current === 0;
                if (nextBtn) nextBtn.disabled = current === pages.length - 1;
                if (currentPageEl) currentPageEl.textContent = current + 1;
            }

            function showPage(index) {
                pages[current].classList.remove('active');
                current = index;
                pages[current].classList.add('active');
                updateButtons();
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    if (current > 0) showPage(current - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (current < pages.length - 1) showPage(current + 1);
                });
            }

            updateButtons();
        }
    </script>
</body>

</html>