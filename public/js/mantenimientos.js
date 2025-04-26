// mantenimientos.js
// Variables globales
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

const limitePorPagina = 10;

function initializeApp() {
    // Cargar datos iniciales
    listarIncidenciasPaginado(1);
    
    // Cargar selectores dinámicamente
    cargarSelectHabitaciones();
    cargarSelectEmpleados();
    
    // Inicializar campos de fecha
    const fechaReporte = document.getElementById('fRep');
    if (fechaReporte) {
        fechaReporte.valueAsDate = new Date();
    }
    
    // Configurar eventos para los modales
    const modalNueva = document.getElementById('modalNuevaIncidencia');
    if (modalNueva) {
        modalNueva.addEventListener('show.bs.modal', () => {
            cargarSelectHabitaciones();
            cargarSelectEmpleados();
        });
    }

    const modalEditar = document.getElementById('modalEditarIncidencia');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', () => {
            cargarSelectHabitaciones();
            cargarSelectEmpleados();
        });
    }
    
    // Cargar estadísticas iniciales
    actualizarEstadisticas();
    
    // Auto-actualización cada 5 minutos
    setInterval(() => {
        listarIncidenciasPaginado(1);
        actualizarEstadisticas();
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
            actualizarEstadisticas(); // Actualizar estadísticas
            // Cerrar el modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaIncidencia'));
            if (modal) modal.hide();
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
            actualizarEstadisticas(); // Actualizar estadísticas
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
                    actualizarEstadisticas(); // Actualizar estadísticas
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
        .then(response => {
            // Manejar tanto array directo como objeto con data
            const habitaciones = Array.isArray(response) ? response : (response.data || []);
            const selectHab = document.getElementById('idHab');
            const selectHabEdit = document.getElementById('id_habitacion_editar');
            
            const options = habitaciones
                .filter(h => h.estado !== 'Ocupada') // Solo mostrar habitaciones no ocupadas
                .map(h => `<option value="${h.id_habitacion}">Habitación ${h.numero_habitacion} (${h.tipo_habitacion})</option>`)
                .join('');
            
            if (selectHab) selectHab.innerHTML = '<option value="">Seleccione una habitación</option>' + options;
            if (selectHabEdit) selectHabEdit.innerHTML = '<option value="">Seleccione una habitación</option>' + options;
        })
        .catch(error => {
            console.error('Error al cargar habitaciones:', error);
            mostrarAlerta('Error al cargar las habitaciones', 'danger');
        });
}

function cargarSelectEmpleados() {
    fetch('../api/empleados.php')
        .then(r => r.json())
        .then(response => {
            // Manejar tanto array directo como objeto con data
            const empleados = Array.isArray(response) ? response : (response.data || []);
            const selectEmp = document.getElementById('idEmp');
            const selectEmpEdit = document.getElementById('id_empleado_editar');
            
            const options = empleados
                .filter(e => e.activo !== false) // Solo mostrar empleados activos si existe el campo
                .map(e => `<option value="${e.id_empleado}">${e.nombre} ${e.apellidos} - ${e.departamento || 'Mantenimiento'}</option>`)
                .join('');
            
            if (selectEmp) selectEmp.innerHTML = '<option value="">Seleccione un empleado</option>' + options;
            if (selectEmpEdit) selectEmpEdit.innerHTML = '<option value="">Seleccione un empleado</option>' + options;
        })
        .catch(error => {
            console.error('Error al cargar empleados:', error);
            mostrarAlerta('Error al cargar los empleados', 'danger');
        });
}

// Funciones de modal
async function abrirModalEditar(id) {
    try {
        // Mostrar indicador de carga
        mostrarAlerta('Cargando datos...', 'info');

        // Primero cargamos los datos de la incidencia
        const response = await fetch(`../api/mantenimiento.php?id=${id}`);
        const inc = await response.json();

        if (!inc.id_incidencia) {
            mostrarAlerta('No se encontró la incidencia', 'danger');
            return;
        }

        // Mostrar el modal primero para que los selectores se inicialicen correctamente
        const modal = new bootstrap.Modal(document.getElementById('modalEditarIncidencia'));
        modal.show();

        // Pequeña pausa para asegurar que el modal está completamente visible
        await new Promise(resolve => setTimeout(resolve, 100));

        // Cargar y establecer los valores
        document.getElementById('id_incidencia_editar').value = inc.id_incidencia;
        document.getElementById('descripcion_editar').value = inc.descripcion;
        document.getElementById('fecha_reporte_editar').value = inc.fecha_reporte;
        document.getElementById('fecha_resolucion_editar').value = inc.fecha_resolucion || '';
        document.getElementById('estado_editar').value = inc.estado;

        // Cargar los selectores
        await Promise.all([
            cargarSelectHabitacionesConSeleccion(inc.id_habitacion),
            cargarSelectEmpleadosConSeleccion(inc.id_empleado)
        ]);

        // Asegurarnos de que los valores están seleccionados
        setTimeout(() => {
            const selectHab = document.getElementById('id_habitacion_editar');
            const selectEmp = document.getElementById('id_empleado_editar');
            if (selectHab) selectHab.value = inc.id_habitacion;
            if (selectEmp) selectEmp.value = inc.id_empleado;
        }, 100);

    } catch (error) {
        console.error('Error al abrir modal de edición:', error);
        mostrarAlerta('Error al cargar los datos de la incidencia', 'danger');
    }
}

// Función para cargar habitaciones y preseleccionar una
async function cargarSelectHabitacionesConSeleccion(idHabitacion) {
    try {
        const response = await fetch('../api/habitaciones.php');
        const data = await response.json();
        const habitaciones = Array.isArray(data) ? data : (data.data || []);
        const selectHabEdit = document.getElementById('id_habitacion_editar');
            
        if (selectHabEdit) {
            // Convertir a número para comparación
            const idHabNum = parseInt(idHabitacion);
            const options = habitaciones.map(h => 
                `<option value="${h.id_habitacion}" ${parseInt(h.id_habitacion) === idHabNum ? 'selected' : ''}>
                    Habitación ${h.numero_habitacion} (${h.tipo_habitacion})
                </option>`
            ).join('');
            
            selectHabEdit.innerHTML = '<option value="">Seleccione una habitación</option>' + options;
            // Forzar la selección
            selectHabEdit.value = idHabitacion;
        }
    } catch (error) {
        console.error('Error al cargar habitaciones:', error);
        mostrarAlerta('Error al cargar las habitaciones', 'danger');
    }
}

// Función para cargar empleados y preseleccionar uno
async function cargarSelectEmpleadosConSeleccion(idEmpleado) {
    try {
        const response = await fetch('../api/empleados.php');
        const data = await response.json();
        const empleados = Array.isArray(data) ? data : (data.data || []);
        const selectEmpEdit = document.getElementById('id_empleado_editar');
            
        if (selectEmpEdit) {
            // Convertir a número para comparación
            const idEmpNum = parseInt(idEmpleado);
            const options = empleados
                .filter(e => e.activo !== false)
                .map(e => 
                    `<option value="${e.id_empleado}" ${parseInt(e.id_empleado) === idEmpNum ? 'selected' : ''}>
                        ${e.nombre} ${e.apellidos} - ${e.departamento || 'Mantenimiento'}
                    </option>`
                ).join('');
            
            selectEmpEdit.innerHTML = '<option value="">Seleccione un empleado</option>' + options;
            // Forzar la selección
            selectEmpEdit.value = idEmpleado;
        }
    } catch (error) {
        console.error('Error al cargar empleados:', error);
        mostrarAlerta('Error al cargar los empleados', 'danger');
    }
}

function cerrarModalEditar() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarIncidencia'));
    if (modal) modal.hide();
}

// Función para actualizar los contadores de estadísticas
async function actualizarEstadisticas() {
    try {
        const response = await fetch('../api/mantenimiento.php');
        const data = await response.json();
        const incidencias = Array.isArray(data) ? data : (data.data || []);
        
        const stats = {
            pendientes: 0,
            'en-proceso': 0,
            resueltas: 0,
            total: incidencias.length
        };

        // Contar incidencias por estado
        incidencias.forEach(inc => {
            switch(inc.estado) {
                case 'Pendiente':
                    stats.pendientes++;
                    break;
                case 'En proceso':
                    stats['en-proceso']++;
                    break;
                case 'Resuelto':
                    stats.resueltas++;
                    break;
            }
        });

        // Actualizar los contadores en el DOM
        Object.entries(stats).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    } catch (error) {
        console.error('Error al actualizar estadísticas:', error);
    }
}
