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
    <title>Control de Asistencia - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header de la página -->
            <div class="page-header">
                <h2 class="page-title">Control de Asistencia</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" onclick="exportarInforme()">
                        <i class="fas fa-file-export me-2"></i>Exportar Informe
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen de asistencia -->
                <div class="grid-container">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-user-check fa-2x text-success mb-3"></i>
                            <div class="stat-value" id="asistenciaHoy">0</div>
                            <div class="stat-label">Presentes Hoy</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-user-clock fa-2x text-warning mb-3"></i>
                            <div class="stat-value" id="retrasos">0</div>
                            <div class="stat-label">Retrasos</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-user-times fa-2x text-danger mb-3"></i>
                            <div class="stat-value" id="ausencias">0</div>
                            <div class="stat-label">Ausencias</div>
                        </div>
                    </div>
                </div>

                <!-- Fichar y Filtros -->
                <div class="row mt-4">
                    <!-- Fichar -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title h5 mb-3">Fichar Entrada/Salida</h3>
                                <div class="mb-3">
                                    <label class="form-label">ID Empleado</label>
                                    <input type="number" id="empFicha" class="form-control" placeholder="Ingrese ID">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Fichaje</label>
                                    <select id="tipoFicha" class="form-select">
                                        <option value="entrada">Entrada</option>
                                        <option value="salida">Salida</option>
                                    </select>
                                </div>
                                <div class="d-grid">
                                    <button class="btn btn-success" onclick="fichar()">
                                        <i class="fas fa-fingerprint me-2"></i>Registrar Fichaje
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title h5 mb-3">Filtros de Búsqueda</h3>
                                <form onsubmit="event.preventDefault(); listar();">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Empleado</label>
                                            <input type="number" id="empFil" class="form-control" placeholder="ID Empleado">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Desde</label>
                                            <input type="date" id="desdeFil" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Hasta</label>
                                            <input type="date" id="hastaFil" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Estado</label>
                                            <select id="estadoFil" class="form-select">
                                                <option value="">Todos</option>
                                                <option value="Puntual">Puntual</option>
                                                <option value="Retraso">Retraso</option>
                                                <option value="Ausente">Ausente</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Buscar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Asistencias -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Registro de Asistencias</h3>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tbAsis">
                                <thead>
                                    <tr>
                                        <th>Empleado</th>
                                        <th>Fecha</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Estado</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = '../api/asistencia.php';

        // Inicialización
        document.addEventListener('DOMContentLoaded', () => {
            establecerFechasDefecto();
            listar();
            actualizarEstadisticas();
        });

        // Establecer fechas por defecto (último mes)
        function establecerFechasDefecto() {
            const hoy = new Date();
            const mesAnterior = new Date(hoy.setMonth(hoy.getMonth() - 1));

            document.getElementById('desdeFil').value = mesAnterior.toISOString().split('T')[0];
            document.getElementById('hastaFil').value = new Date().toISOString().split('T')[0];
        }

        // Registrar fichaje
        function fichar() {
            const id_empleado = document.getElementById('empFicha').value;
            const tipo = document.getElementById('tipoFicha').value;

            if (!id_empleado) {
                mostrarAlerta('Introduce ID empleado', 'warning');
                return;
            }

            const datos = new URLSearchParams({
                id_empleado,
                tipo
            });

            fetch(API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: datos
                })
                .then(r => r.json())
                .then(res => {
                    if (res.error) {
                        mostrarAlerta(res.error, 'danger');
                        return;
                    }
                    mostrarAlerta('Fichaje registrado correctamente', 'success');
                    document.getElementById('empFicha').value = '';
                    listar();
                    actualizarEstadisticas();
                });
        }

        // Listar asistencias
        function listar() {
            const params = new URLSearchParams();
            const emp = document.getElementById('empFil').value;
            const desde = document.getElementById('desdeFil').value;
            const hasta = document.getElementById('hastaFil').value;
            const estado = document.getElementById('estadoFil').value;

            if (emp) params.append('emp', emp);
            if (desde) params.append('desde', desde);
            if (hasta) params.append('hasta', hasta);
            if (estado) params.append('estado', estado);

            fetch(`${API}?${params}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.querySelector('#tbAsis tbody');
                    tbody.innerHTML = '';

                    data.forEach(a => {
                        const estado = determinarEstado(a);
                        const esHoy = a.fecha === new Date().toISOString().split('T')[0];
                        let acciones = `
                            <button class="btn btn-sm btn-outline-primary" onclick="editarAsistencia(${a.id_asistencia})">
                                <i class="fas fa-edit"></i>
                            </button>
                        `;
                        if (esHoy && a.hora_entrada && !a.hora_salida) {
                            acciones += `
                                <button class="btn btn-sm btn-outline-success ms-1" onclick="ficharSalidaDesdeTabla(${a.id_empleado})">
                                    <i class="fas fa-sign-out-alt"></i> Fichar salida
                                </button>
                            `;
                        }
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr>
                                <td>${a.nombre ? a.nombre + ' ' + a.apellidos : a.id_empleado}</td>
                                <td>${a.fecha}</td>
                                <td>${a.hora_entrada || '-'}</td>
                                <td>${a.hora_salida || '-'}</td>
                                <td>
                                    <span class="badge bg-${estado.color}">${estado.texto}</span>
                                </td>
                                <td>${a.observaciones || '-'}</td>
                                <td>${acciones}</td>
                            </tr>
                        `);
                    });

                    actualizarEstadisticas(data);
                });
        }

        // Acción rápida de fichar salida desde la tabla
        function ficharSalidaDesdeTabla(id_empleado) {
            fetch(API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id_empleado,
                        tipo: 'salida'
                    })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        mostrarAlerta('Salida registrada correctamente', 'success');
                        listar();
                    } else {
                        mostrarAlerta(res.error || 'Error al fichar salida', 'danger');
                    }
                });
        }

        // Determinar estado de asistencia
        function determinarEstado(asistencia) {
            if (!asistencia.hora_entrada) {
                return {
                    texto: 'Ausente',
                    color: 'danger'
                };
            }

            const entrada = new Date(`${asistencia.fecha} ${asistencia.hora_entrada}`);
            const horaLimite = new Date(entrada);
            horaLimite.setMinutes(horaLimite.getMinutes() + 15); // 15 minutos de tolerancia

            return entrada > horaLimite ? {
                texto: 'Retraso',
                color: 'warning'
            } : {
                texto: 'Puntual',
                color: 'success'
            };
        }

        // Actualizar estadísticas
        function actualizarEstadisticas(data = []) {
            const hoy = new Date().toISOString().split('T')[0];
            const asistenciasHoy = data.filter(a => a.fecha === hoy);

            document.getElementById('asistenciaHoy').textContent =
                asistenciasHoy.filter(a => a.hora_entrada).length;

            document.getElementById('retrasos').textContent =
                asistenciasHoy.filter(a => determinarEstado(a).texto === 'Retraso').length;

            document.getElementById('ausencias').textContent =
                asistenciasHoy.filter(a => !a.hora_entrada).length;
        }

        // Editar asistencia
        function editarAsistencia(id) {
            // Buscar el registro por ID
            fetch(`${API}?id=${id}`)
                .then(r => r.json())
                .then(a => {
                    mostrarModalEdicion(a);
                });
        }

        // Mostrar modal de edición
        function mostrarModalEdicion(asistencia) {
            // Crear modal si no existe
            let modal = document.getElementById('modalEditarAsistencia');
            if (!modal) {
                modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'modalEditarAsistencia';
                modal.tabIndex = -1;
                modal.innerHTML = `
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Editar Asistencia</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <form id="formEditarAsistencia">
                        <input type="hidden" id="edit_id_asistencia">
                        <div class="mb-3">
                          <label class="form-label">Hora Entrada</label>
                          <input type="time" class="form-control" id="edit_hora_entrada">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Hora Salida</label>
                          <input type="time" class="form-control" id="edit_hora_salida">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Observaciones</label>
                          <input type="text" class="form-control" id="edit_observaciones">
                        </div>
                      </form>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button type="button" class="btn btn-primary" id="btnGuardarEdicion">Guardar</button>
                    </div>
                  </div>
                </div>`;
                document.body.appendChild(modal);
            }
            // Rellenar campos
            document.getElementById('edit_id_asistencia').value = asistencia.id_asistencia;
            document.getElementById('edit_hora_entrada').value = asistencia.hora_entrada ? asistencia.hora_entrada.substring(0, 5) : '';
            document.getElementById('edit_hora_salida').value = asistencia.hora_salida ? asistencia.hora_salida.substring(0, 5) : '';
            document.getElementById('edit_observaciones').value = asistencia.observaciones || '';

            // Evento guardar
            document.getElementById('btnGuardarEdicion').onclick = function() {
                guardarEdicionAsistencia();
            };

            // Mostrar modal
            new bootstrap.Modal(modal).show();
        }

        // Guardar edición
        function guardarEdicionAsistencia() {
            const id = document.getElementById('edit_id_asistencia').value;
            const hora_entrada = document.getElementById('edit_hora_entrada').value;
            const hora_salida = document.getElementById('edit_hora_salida').value;
            const observaciones = document.getElementById('edit_observaciones').value;
            const datos = new URLSearchParams({
                hora_entrada,
                hora_salida,
                observaciones
            });
            fetch(`${API}?id=${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: datos
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        mostrarAlerta('Asistencia actualizada', 'success');
                        listar();
                    } else {
                        mostrarAlerta(res.error || 'Error al actualizar', 'danger');
                    }
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarAsistencia'));
                    if (modal) modal.hide();
                });
        }

        // Exportar informe
        function exportarInforme() {
            const filas = document.querySelectorAll('#tbAsis tbody tr');
            if (!filas.length) {
                mostrarAlerta('No hay datos para exportar', 'warning');
                return;
            }
            let csv = 'Empleado,Fecha,Entrada,Salida,Estado,Observaciones\n';
            filas.forEach(tr => {
                const celdas = tr.querySelectorAll('td');
                const fila = Array.from(celdas).slice(0, 6).map(td => '"' + td.textContent.replace(/"/g, '""') + '"').join(',');
                csv += fila + '\n';
            });
            const blob = new Blob([csv], {
                type: 'text/csv'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'informe_asistencia.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Mostrar alertas
        function mostrarAlerta(mensaje, tipo) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            alertDiv.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
</body>

</html>