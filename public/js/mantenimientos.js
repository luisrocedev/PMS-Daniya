// mantenimientos.js
// Variables globales
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

const limitePorPagina = 10;

function initializeApp() {
    listarIncidenciasPaginado(1);
    cargarSelectHabitaciones();
    cargarSelectEmpleados();
    
    // Inicializar campos de fecha
    document.getElementById('fRep').valueAsDate = new Date();
    
    // Auto-actualización cada 5 minutos
    setInterval(() => {
        listarIncidenciasPaginado(1);
    }, 300000);
}

// Función para listar incidencias con paginación y filtros
function listarIncidenciasPaginado(pagina = 1) {
    const busqueda = document.getElementById('searchMant')?.value || '';
    const estado = document.getElementById('estadoMant')?.value || '';

    let url = `../api/mantenimiento.php?page=${pagina}&limit=${limitePorPagina}`;
    if (busqueda) url += `&search=${encodeURIComponent(busqueda)}`;
    if (estado) url += `&estado=${encodeURIComponent(estado)}`;

    fetch(url)
        .then(r => r.json())
        .then(obj => {
            const data = obj.data || [];
            const total = obj.total || 0;
            const paginaActual = obj.page || 1;
            
            renderTablaIncidencias(data);
            renderPaginacion(paginaActual, limitePorPagina, total);
        })
        .catch(error => {
            console.error('Error al listar incidencias:', error);
            mostrarAlerta('Error al cargar las incidencias', 'danger');
        });
}

// Renderizar tabla de incidencias
function renderTablaIncidencias(incidencias) {
    const tbody = document.getElementById('tabla-mant');
    if (!tbody) return;

    tbody.innerHTML = '';
    incidencias.forEach(inc => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${inc.id_incidencia}</td>
            <td>${inc.id_habitacion}</td>
            <td>${inc.id_empleado}</td>
            <td>${inc.descripcion}</td>
            <td>${formatearFecha(inc.fecha_reporte)}</td>
            <td>${inc.fecha_resolucion ? formatearFecha(inc.fecha_resolucion) : '-'}</td>
            <td><span class="badge bg-${getEstadoColor(inc.estado)}">${inc.estado}</span></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-primary" onclick="abrirModalEditar(${inc.id_incidencia})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger" onclick="confirmarEliminar(${inc.id_incidencia})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Renderizar paginación
function renderPaginacion(pagina, limite, total) {
    const divPag = document.getElementById('paginacionMant');
    if (!divPag) return;

    const totalPaginas = Math.ceil(total / limite);
    let html = '<nav aria-label="Navegación de incidencias"><ul class="pagination justify-content-center">';
    
    // Botón anterior
    html += `
        <li class="page-item ${pagina <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarIncidenciasPaginado(${pagina - 1})" tabindex="-1">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;

    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        html += `
            <li class="page-item ${pagina === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); listarIncidenciasPaginado(${i})">${i}</a>
            </li>
        `;
    }

    // Botón siguiente
    html += `
        <li class="page-item ${pagina >= totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarIncidenciasPaginado(${pagina + 1})">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;

    html += '</ul></nav>';
    divPag.innerHTML = html;
}

// Crear nueva incidencia
function crearIncidencia(e) {
    e.preventDefault();
    
    const formData = {
        id_habitacion: document.getElementById('idHab').value,
        id_empleado: document.getElementById('idEmp').value,
        descripcion: document.getElementById('descMant').value,
        fecha_reporte: document.getElementById('fRep').value,
        fecha_resolucion: document.getElementById('fRes').value || null,
        estado: document.getElementById('estMant').value
    };

    fetch('../api/mantenimiento.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Incidencia creada con éxito', 'success');
            document.getElementById('formCrearIncidencia').reset();
            document.getElementById('fRep').valueAsDate = new Date();
            listarIncidenciasPaginado(1);
        } else {
            mostrarAlerta(data.error || 'Error al crear la incidencia', 'danger');
        }
    })
    .catch(error => {
        console.error('Error al crear incidencia:', error);
        mostrarAlerta('Error al crear la incidencia', 'danger');
    });
}

// Editar incidencia
function editarIncidencia(e) {
    e.preventDefault();
    
    const id = document.getElementById('id_incidencia_editar').value;
    const formData = {
        id_habitacion: document.getElementById('id_habitacion_editar').value,
        id_empleado: document.getElementById('id_empleado_editar').value,
        descripcion: document.getElementById('descripcion_editar').value,
        fecha_reporte: document.getElementById('fecha_reporte_editar').value,
        fecha_resolucion: document.getElementById('fecha_resolucion_editar').value || null,
        estado: document.getElementById('estado_editar').value
    };

    fetch(`../api/mantenimiento.php?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Incidencia actualizada con éxito', 'success');
            cerrarModalEditar();
            listarIncidenciasPaginado(1);
        } else {
            mostrarAlerta(data.error || 'Error al actualizar la incidencia', 'danger');
        }
    })
    .catch(error => {
        console.error('Error al actualizar incidencia:', error);
        mostrarAlerta('Error al actualizar la incidencia', 'danger');
    });
}

// Eliminar incidencia
function confirmarEliminar(id) {
    if (confirm('¿Está seguro de que desea eliminar esta incidencia?')) {
        fetch(`../api/mantenimiento.php?id=${id}`, { method: 'DELETE' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Incidencia eliminada con éxito', 'success');
                    listarIncidenciasPaginado(1);
                } else {
                    mostrarAlerta(data.error || 'Error al eliminar la incidencia', 'danger');
                }
            })
            .catch(error => {
                console.error('Error al eliminar incidencia:', error);
                mostrarAlerta('Error al eliminar la incidencia', 'danger');
            });
    }
}

// Funciones auxiliares
function getEstadoColor(estado) {
    const colores = {
        'Pendiente': 'warning',
        'En proceso': 'info',
        'Resuelto': 'success'
    };
    return colores[estado] || 'secondary';
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function mostrarAlerta(mensaje, tipo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Cargar selects dinámicos
function cargarSelectHabitaciones() {
    fetch('../api/habitaciones.php')
        .then(r => r.json())
        .then(data => {
            const habitaciones = Array.isArray(data) ? data : [];
            const selectHab = document.getElementById('idHab');
            const selectHabEdit = document.getElementById('id_habitacion_editar');
            
            const options = habitaciones.map(h => 
                `<option value="${h.id_habitacion}">Habitación ${h.numero_habitacion}</option>`
            ).join('');
            
            if (selectHab) selectHab.innerHTML = '<option value="">Seleccione una habitación</option>' + options;
            if (selectHabEdit) selectHabEdit.innerHTML = '<option value="">Seleccione una habitación</option>' + options;
        });
}

function cargarSelectEmpleados() {
    fetch('../api/empleados.php')
        .then(r => r.json())
        .then(data => {
            const empleados = Array.isArray(data) ? data : [];
            const selectEmp = document.getElementById('idEmp');
            const selectEmpEdit = document.getElementById('id_empleado_editar');
            
            const options = empleados.map(e => 
                `<option value="${e.id_empleado}">${e.nombre} ${e.apellidos}</option>`
            ).join('');
            
            if (selectEmp) selectEmp.innerHTML = '<option value="">Seleccione un empleado</option>' + options;
            if (selectEmpEdit) selectEmpEdit.innerHTML = '<option value="">Seleccione un empleado</option>' + options;
        });
}

// Funciones de modal
function abrirModalEditar(id) {
    fetch(`../api/mantenimiento.php?id=${id}`)
        .then(r => r.json())
        .then(inc => {
            if (!inc.id_incidencia) {
                mostrarAlerta('No se encontró la incidencia', 'danger');
                return;
            }
            
            document.getElementById('id_incidencia_editar').value = inc.id_incidencia;
            document.getElementById('id_habitacion_editar').value = inc.id_habitacion;
            document.getElementById('id_empleado_editar').value = inc.id_empleado;
            document.getElementById('descripcion_editar').value = inc.descripcion;
            document.getElementById('fecha_reporte_editar').value = inc.fecha_reporte;
            document.getElementById('fecha_resolucion_editar').value = inc.fecha_resolucion || '';
            document.getElementById('estado_editar').value = inc.estado;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarIncidencia'));
            modal.show();
        })
        .catch(error => {
            console.error('Error al abrir modal de edición:', error);
            mostrarAlerta('Error al cargar los datos de la incidencia', 'danger');
        });
}

function cerrarModalEditar() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarIncidencia'));
    if (modal) modal.hide();
}
