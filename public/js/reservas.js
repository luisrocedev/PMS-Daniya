// reservas.js
// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', () => {
    listarReservasPaginado(1);
    renderCalendar();
    // Auto-actualización cada 30 segundos
    setInterval(() => {
        listarReservasPaginado(1);
        if (calendar) {
            calendar.refetchEvents();
        }
    }, 30000);
});

// Variables globales
let calendar;
const limitReservas = 10;

// Función para listar reservas con paginación y filtros
function listarReservasPaginado(page = 1) {
    const searchVal = document.getElementById('searchRes')?.value || '';
    const estadoVal = document.getElementById('estadoRes')?.value || '';
    const fechaInicio = document.getElementById('fechaInicioFiltro')?.value || '';
    const fechaFin = document.getElementById('fechaFinFiltro')?.value || '';

    let url = `../api/reservas.php?page=${page}&limit=${limitReservas}`;
    if (searchVal) url += `&search=${encodeURIComponent(searchVal)}`;
    if (estadoVal) url += `&estado=${encodeURIComponent(estadoVal)}`;
    if (fechaInicio) url += `&fecha_inicio=${encodeURIComponent(fechaInicio)}`;
    if (fechaFin) url += `&fecha_fin=${encodeURIComponent(fechaFin)}`;

    fetch(url)
        .then(r => r.json())
        .then(obj => {
            const data = obj.data || [];
            const total = obj.total || 0;
            const pageNum = obj.page || 1;
            renderTablaReservas(data);
            renderPaginacion(pageNum, limitReservas, total);
        })
        .catch(e => {
            console.error('Error al listar reservas:', e);
            mostrarAlerta('Error al cargar las reservas', 'danger');
        });
}

// Renderizar tabla de reservas
function renderTablaReservas(reservas) {
    const tbody = document.getElementById('tabla-reservas');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    reservas.forEach(res => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${res.id_reserva}</td>
            <td>${res.id_cliente}</td>
            <td>${res.id_habitacion}</td>
            <td>${formatearFecha(res.fecha_entrada)}</td>
            <td>${formatearFecha(res.fecha_salida)}</td>
            <td><span class="badge bg-${getEstadoColor(res.estado_reserva)}">${res.estado_reserva}</span></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-primary" onclick="abrirModalEditar(${res.id_reserva})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger" onclick="confirmarEliminar(${res.id_reserva})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Renderizar paginación
function renderPaginacion(page, limit, total) {
    const divPag = document.getElementById('paginacionReservas');
    if (!divPag) return;

    const totalPages = Math.ceil(total / limit);
    let html = '<nav aria-label="Navegación de reservas"><ul class="pagination justify-content-center">';
    
    // Botón anterior
    html += `
        <li class="page-item ${page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarReservasPaginado(${page - 1})" tabindex="-1">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;

    // Páginas
    for (let i = 1; i <= totalPages; i++) {
        html += `
            <li class="page-item ${page === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); listarReservasPaginado(${i})">${i}</a>
            </li>
        `;
    }

    // Botón siguiente
    html += `
        <li class="page-item ${page >= totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarReservasPaginado(${page + 1})">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;

    html += '</ul></nav>';
    divPag.innerHTML = html;
}

// Crear nueva reserva
function crearReserva(esDesdeCalendario = false) {
    const formId = esDesdeCalendario ? 'formNuevaReservaCalendario' : 'formCrearReserva';
    const form = document.getElementById(formId);
    if (!form) return;

    const formData = {
        id_cliente: document.getElementById(esDesdeCalendario ? 'id_cliente_cal' : 'id_cliente_nueva').value,
        id_habitacion: document.getElementById(esDesdeCalendario ? 'habitacion_disp' : 'id_habitacion_nueva').value,
        fecha_entrada: document.getElementById(esDesdeCalendario ? 'fecha_entrada_cal' : 'fecha_entrada_nueva').value,
        fecha_salida: document.getElementById(esDesdeCalendario ? 'fecha_salida_cal' : 'fecha_salida_nueva').value,
        estado_reserva: esDesdeCalendario ? 'Pendiente' : document.getElementById('estado_nueva').value
    };

    fetch('../api/reservas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Reserva creada con éxito', 'success');
            form.reset();
            if (esDesdeCalendario) {
                cerrarModalNuevaReservaCalendario();
            }
            listarReservasPaginado(1);
            if (calendar) calendar.refetchEvents();
        } else {
            mostrarAlerta(data.error || 'Error al crear la reserva', 'danger');
        }
    })
    .catch(e => {
        console.error('Error al crear reserva:', e);
        mostrarAlerta('Error al crear la reserva', 'danger');
    });
}

// Editar reserva
function editarReserva() {
    const id_reserva = document.getElementById('id_reserva_editar').value;
    const formData = {
        id_cliente: document.getElementById('id_cliente_editar').value,
        id_habitacion: document.getElementById('id_habitacion_editar').value,
        fecha_entrada: document.getElementById('fecha_entrada_editar').value,
        fecha_salida: document.getElementById('fecha_salida_editar').value,
        estado_reserva: document.getElementById('estado_editar').value
    };

    fetch(`../api/reservas.php?id=${id_reserva}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Reserva actualizada con éxito', 'success');
            cerrarModalEditar();
            listarReservasPaginado(1);
            if (calendar) calendar.refetchEvents();
        } else {
            mostrarAlerta(data.error || 'Error al actualizar la reserva', 'danger');
        }
    })
    .catch(e => {
        console.error('Error al actualizar reserva:', e);
        mostrarAlerta('Error al actualizar la reserva', 'danger');
    });
}

// Eliminar reserva
function confirmarEliminar(idReserva) {
    if (confirm('¿Está seguro de que desea eliminar esta reserva?')) {
        fetch(`../api/reservas.php?id=${idReserva}`, { method: 'DELETE' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Reserva eliminada con éxito', 'success');
                    listarReservasPaginado(1);
                    if (calendar) calendar.refetchEvents();
                } else {
                    mostrarAlerta(data.error || 'Error al eliminar la reserva', 'danger');
                }
            })
            .catch(e => {
                console.error('Error al eliminar reserva:', e);
                mostrarAlerta('Error al eliminar la reserva', 'danger');
            });
    }
}

// Funciones del calendario
function renderCalendar(filterTipo = '') {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    if (calendar) calendar.destroy();
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        editable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        dateClick: function(info) {
            abrirModalNuevaReservaCalendario(info.dateStr);
        },
        eventDrop: function(info) {
            actualizarFechasReserva(info);
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            let url = `../api/reservas_calendar.php?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`;
            if (filterTipo) url += `&tipo=${encodeURIComponent(filterTipo)}`;
            
            fetch(url)
                .then(response => response.json())
                .then(events => successCallback(events))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            abrirModalEditar(info.event.id);
        }
    });

    calendar.render();
}

// Función para buscar habitaciones disponibles
function buscarDisponibles() {
    const fechaEntrada = document.getElementById('fecha_entrada_cal').value;
    const fechaSalida = document.getElementById('fecha_salida_cal').value;
    
    if (!fechaEntrada || !fechaSalida) {
        mostrarAlerta('Debe indicar fecha de entrada y salida', 'warning');
        return;
    }

    const url = `../api/disponibilidad.php?fecha_inicio=${encodeURIComponent(fechaEntrada)}&fecha_fin=${encodeURIComponent(fechaSalida)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(obj => {
            const select = document.getElementById('habitacion_disp');
            select.innerHTML = '<option value="">Seleccione</option>';
            
            if (obj.data && obj.data.length > 0) {
                obj.data.forEach(hab => {
                    const option = document.createElement('option');
                    option.value = hab.id_habitacion;
                    option.textContent = `${hab.numero_habitacion} - ${hab.tipo_habitacion}`;
                    select.appendChild(option);
                });
            } else {
                mostrarAlerta('No hay habitaciones disponibles para el rango seleccionado', 'warning');
            }
        })
        .catch(e => {
            console.error('Error al buscar habitaciones disponibles:', e);
            mostrarAlerta('Error al buscar habitaciones disponibles', 'danger');
        });
}

// Funciones auxiliares
function getEstadoColor(estado) {
    const colores = {
        'Pendiente': 'warning',
        'Confirmada': 'primary',
        'Cancelada': 'danger',
        'CheckIn': 'success',
        'CheckOut': 'secondary'
    };
    return colores[estado] || 'info';
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

// Funciones de modal
function abrirModalEditar(idReserva) {
    fetch(`../api/reservas.php?id=${idReserva}`)
        .then(r => r.json())
        .then(res => {
            if (!res.id_reserva) {
                mostrarAlerta('No se encontró la reserva', 'danger');
                return;
            }
            document.getElementById('id_reserva_editar').value = res.id_reserva;
            document.getElementById('id_cliente_editar').value = res.id_cliente;
            document.getElementById('id_habitacion_editar').value = res.id_habitacion;
            document.getElementById('fecha_entrada_editar').value = res.fecha_entrada;
            document.getElementById('fecha_salida_editar').value = res.fecha_salida;
            document.getElementById('estado_editar').value = res.estado_reserva;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarReserva'));
            modal.show();
        })
        .catch(e => {
            console.error('Error al abrir modal de edición:', e);
            mostrarAlerta('Error al cargar los datos de la reserva', 'danger');
        });
}

function actualizarFechasReserva(info) {
    const idReserva = info.event.id;
    const newStart = info.event.startStr;
    const newEnd = info.event.endStr || newStart;

    fetch(`../api/reservas.php?id=${idReserva}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            fecha_entrada: newStart,
            fecha_salida: newEnd
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Fechas actualizadas correctamente', 'success');
            listarReservasPaginado(1);
        } else {
            mostrarAlerta(data.error || 'Error al actualizar las fechas', 'danger');
            info.revert();
        }
    })
    .catch(e => {
        console.error('Error al actualizar fechas:', e);
        mostrarAlerta('Error al actualizar las fechas', 'danger');
        info.revert();
    });
}
