// reservas.js
// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', () => {
    listarReservas();
    renderCalendar();
    actualizarEstadisticas(); // Nueva función para actualizar los contadores
    
    // Auto-actualización cada 30 segundos
    setInterval(() => {
        listarReservas();
        actualizarEstadisticas();
        if (calendar) {
            calendar.refetchEvents();
        }
    }, 30000);
});

// Variables globales
let calendar;

// Función para listar todas las reservas sin paginación
function listarReservas() {
    const searchVal = document.getElementById('searchRes')?.value || '';
    const estadoVal = document.getElementById('estadoRes')?.value || '';
    const fechaInicio = document.getElementById('fechaInicioFiltro')?.value || '';
    const fechaFin = document.getElementById('fechaFinFiltro')?.value || '';

    let url = `../api/reservas.php?`;
    if (searchVal) url += `&search=${encodeURIComponent(searchVal)}`;
    if (estadoVal) url += `&estado=${encodeURIComponent(estadoVal)}`;
    if (fechaInicio) url += `&fecha_inicio=${encodeURIComponent(fechaInicio)}`;
    if (fechaFin) url += `&fecha_fin=${encodeURIComponent(fechaFin)}`;

    fetch(url)
        .then(r => r.json())
        .then(obj => {
            // Si es un array, usamos directamente, si es un objeto con data, extraemos data
            const data = Array.isArray(obj) ? obj : (obj.data || []);
            renderTablaReservas(data);
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

    // Añadir campos adicionales si existen
    if (!esDesdeCalendario) {
        const numPersonas = document.getElementById('num_personas');
        const observaciones = document.getElementById('observaciones');
        if (numPersonas) formData.num_personas = numPersonas.value;
        if (observaciones) formData.observaciones = observaciones.value;
    }

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
            const modal = bootstrap.Modal.getInstance(document.querySelector(esDesdeCalendario ? '#modalNuevaReservaCalendario' : '#modalNuevaReserva'));
            if (modal) modal.hide();
            listarReservas();
            actualizarEstadisticas();
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

    // Añadir campos adicionales si existen
    const numPersonas = document.getElementById('num_personas_editar');
    const observaciones = document.getElementById('observaciones_editar');
    if (numPersonas) formData.num_personas = numPersonas.value;
    if (observaciones) formData.observaciones = observaciones.value;

    fetch(`../api/reservas.php?id=${id_reserva}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Reserva actualizada con éxito', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarReserva'));
            if (modal) modal.hide();
            listarReservas();
            actualizarEstadisticas();
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
                    listarReservas();
                    actualizarEstadisticas();
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

// Actualizar las estadísticas
function actualizarEstadisticas() {
    // Podemos hacer una llamada a la API para obtener las estadísticas
    // o bien calcularlas a partir de las reservas existentes
    fetch('../api/reservas.php?estadisticas=true')
        .then(r => r.json())
        .then(stats => {
            // Actualizamos los contadores (si existe la API que devuelve estadísticas)
            document.getElementById('reservasActivas').textContent = stats.activas || 0;
            document.getElementById('checkinHoy').textContent = stats.checkins_hoy || 0;
            document.getElementById('checkoutHoy').textContent = stats.checkouts_hoy || 0;
            document.getElementById('ocupacionHoy').textContent = (stats.ocupacion_actual || 0) + '%';
        })
        .catch(e => {
            console.error('Error al cargar estadísticas:', e);
            // Si falla, ponemos valores por defecto
            document.getElementById('reservasActivas').textContent = '0';
            document.getElementById('checkinHoy').textContent = '0';
            document.getElementById('checkoutHoy').textContent = '0';
            document.getElementById('ocupacionHoy').textContent = '0%';
        });
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

// Función para actualizar el calendario según el filtro seleccionado
function actualizarCalendario() {
    const filterTipo = document.getElementById('filtroTipoHab').value || '';
    renderCalendar(filterTipo);
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
    alertDiv.style.zIndex = '9999';
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
            
            if (document.getElementById('num_personas_editar')) {
                document.getElementById('num_personas_editar').value = res.num_personas || 1;
            }
            
            if (document.getElementById('observaciones_editar')) {
                document.getElementById('observaciones_editar').value = res.observaciones || '';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarReserva'));
            modal.show();
        })
        .catch(e => {
            console.error('Error al abrir modal de edición:', e);
            mostrarAlerta('Error al cargar los datos de la reserva', 'danger');
        });
}

function abrirModalNuevaReservaCalendario(fecha) {
    const fechaEntradaEl = document.getElementById('fecha_entrada_cal');
    if (fechaEntradaEl) fechaEntradaEl.value = fecha;
    
    const modal = new bootstrap.Modal(document.getElementById('modalNuevaReservaCalendario'));
    modal.show();
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
            listarReservas();
            actualizarEstadisticas();
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
