let currentPage = 1;
const itemsPerPage = 10;

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    cargarTiposHabitacion();
    cargarEstadisticas();
    cargarTarifas(1);
    
    // Listeners para vista previa en tiempo real
    document.getElementById('nombreTarifa').addEventListener('input', actualizarVistaPrevia);
    document.getElementById('tipoHabitacion').addEventListener('change', actualizarVistaPrevia);
    document.getElementById('precio').addEventListener('input', actualizarVistaPrevia);
    document.getElementById('temporada').addEventListener('change', actualizarVistaPrevia);
    document.getElementById('fecha_inicio').addEventListener('change', actualizarVistaPrevia);
    document.getElementById('fecha_fin').addEventListener('change', actualizarVistaPrevia);

    // Listener para filtros
    document.getElementById('form-filtros').addEventListener('submit', (e) => {
        e.preventDefault();
        cargarTarifas(1);
    });
});

// Funciones de carga de datos
async function cargarTiposHabitacion() {
    try {
        const response = await fetch('../api/tipos_habitacion.php');
        const data = await response.json();
        
        if (data.success) {
            const selectTipos = document.getElementById('tipoHabitacion');
            const selectFiltro = document.getElementById('filtro-tipo');
            
            data.data.forEach(tipo => {
                selectTipos.innerHTML += `<option value="${tipo.id}">${tipo.nombre}</option>`;
                selectFiltro.innerHTML += `<option value="${tipo.id}">${tipo.nombre}</option>`;
            });
        }
    } catch (error) {
        mostrarError('Error al cargar tipos de habitación');
    }
}

async function cargarEstadisticas() {
    try {
        const response = await fetch('../api/tarifas.php?stats=true');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-tarifas').textContent = data.stats.total;
            document.getElementById('tarifa-promedio').textContent = data.stats.promedio + '€';
            document.getElementById('tarifas-activas').textContent = data.stats.activas;
            document.getElementById('tipos-habitacion').textContent = data.stats.tipos;
        }
    } catch (error) {
        mostrarError('Error al cargar estadísticas');
    }
}

async function cargarTarifas(pagina) {
    currentPage = pagina;
    const filtros = obtenerFiltros();
    
    try {
        const response = await fetch(`../api/tarifas.php?page=${pagina}&limit=${itemsPerPage}&${new URLSearchParams(filtros)}`);
        const data = await response.json();
        
        if (data.success) {
            renderizarTarifas(data.data);
            renderizarPaginacion(data.total);
        }
    } catch (error) {
        mostrarError('Error al cargar tarifas');
    }
}

// Funciones de renderizado
function renderizarTarifas(tarifas) {
    const tbody = document.getElementById('tabla-tarifas');
    tbody.innerHTML = '';
    
    tarifas.forEach(tarifa => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${tarifa.id}</td>
            <td>${tarifa.nombre}</td>
            <td>${tarifa.tipo_habitacion}</td>
            <td class="text-end">${tarifa.precio}€</td>
            <td>${tarifa.temporada}</td>
            <td>${formatearFecha(tarifa.fecha_inicio)}</td>
            <td>${formatearFecha(tarifa.fecha_fin)}</td>
            <td class="text-center">
                <span class="badge bg-${tarifa.estado === 'Activa' ? 'success' : 'danger'}">
                    ${tarifa.estado}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-info me-1" onclick="verDetalleTarifa(${tarifa.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-primary me-1" onclick="editarTarifa(${tarifa.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="eliminarTarifa(${tarifa.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderizarPaginacion(total) {
    const totalPages = Math.ceil(total / itemsPerPage);
    const paginacion = document.getElementById('paginacion-tarifas');
    
    let html = `
        <div>
            Mostrando ${(currentPage - 1) * itemsPerPage + 1} - ${Math.min(currentPage * itemsPerPage, total)} de ${total}
        </div>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary" ${currentPage === 1 ? 'disabled' : ''} 
                onclick="cargarTarifas(${currentPage - 1})">
                <i class="fas fa-chevron-left"></i>
            </button>
    `;
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <button class="btn btn-sm btn-outline-secondary ${i === currentPage ? 'active' : ''}" 
                    onclick="cargarTarifas(${i})">${i}</button>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<button class="btn btn-sm btn-outline-secondary" disabled>...</button>`;
        }
    }
    
    html += `
            <button class="btn btn-sm btn-outline-secondary" ${currentPage === totalPages ? 'disabled' : ''} 
                onclick="cargarTarifas(${currentPage + 1})">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    `;
    
    paginacion.innerHTML = html;
}

// Funciones de gestión de tarifas
async function guardarTarifa(event) {
    event.preventDefault();
    
    const formData = {
        nombre: document.getElementById('nombreTarifa').value,
        tipo_habitacion_id: document.getElementById('tipoHabitacion').value,
        precio: document.getElementById('precio').value,
        temporada: document.getElementById('temporada').value,
        fecha_inicio: document.getElementById('fecha_inicio').value,
        fecha_fin: document.getElementById('fecha_fin').value
    };
    
    try {
        const response = await fetch('../api/tarifas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        if (data.success) {
            mostrarExito('Tarifa guardada correctamente');
            bootstrap.Modal.getInstance(document.getElementById('modalTarifa')).hide();
            cargarTarifas(currentPage);
            cargarEstadisticas();
        } else {
            mostrarError(data.error || 'Error al guardar la tarifa');
        }
    } catch (error) {
        mostrarError('Error al guardar la tarifa');
    }
}

async function editarTarifa(id) {
    try {
        const response = await fetch(`../api/tarifas.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const tarifa = data.data;
            document.getElementById('nombreTarifa').value = tarifa.nombre;
            document.getElementById('tipoHabitacion').value = tarifa.tipo_habitacion_id;
            document.getElementById('precio').value = tarifa.precio;
            document.getElementById('temporada').value = tarifa.temporada;
            document.getElementById('fecha_inicio').value = tarifa.fecha_inicio;
            document.getElementById('fecha_fin').value = tarifa.fecha_fin;
            
            document.getElementById('modalTarifaLabel').textContent = 'Editar Tarifa';
            actualizarVistaPrevia();
            
            const modal = new bootstrap.Modal(document.getElementById('modalTarifa'));
            modal.show();
        }
    } catch (error) {
        mostrarError('Error al cargar la tarifa');
    }
}

async function eliminarTarifa(id) {
    if (!confirm('¿Está seguro de eliminar esta tarifa?')) return;
    
    try {
        const response = await fetch(`../api/tarifas.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        if (data.success) {
            mostrarExito('Tarifa eliminada correctamente');
            cargarTarifas(currentPage);
            cargarEstadisticas();
        } else {
            mostrarError(data.error || 'Error al eliminar la tarifa');
        }
    } catch (error) {
        mostrarError('Error al eliminar la tarifa');
    }
}

async function verDetalleTarifa(id) {
    try {
        const response = await fetch(`../api/tarifas.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const tarifa = data.data;
            document.getElementById('detalleTarifaId').textContent = tarifa.id;
            document.getElementById('detalleTarifaNombre').textContent = tarifa.nombre;
            document.getElementById('detalleTarifaTipo').textContent = tarifa.tipo_habitacion;
            document.getElementById('detalleTarifaPrecio').textContent = tarifa.precio + '€';
            document.getElementById('detalleTarifaTemporada').textContent = tarifa.temporada;
            document.getElementById('detalleTarifaInicio').textContent = formatearFecha(tarifa.fecha_inicio);
            document.getElementById('detalleTarifaFin').textContent = formatearFecha(tarifa.fecha_fin);
            
            const modal = new bootstrap.Modal(document.getElementById('modalDetalleTarifa'));
            modal.show();
        }
    } catch (error) {
        mostrarError('Error al cargar los detalles de la tarifa');
    }
}

// Modal de confirmación de iteración
function mostrarConfirmacionIteracion(callback) {
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmacionIteracion'));
    const btnConfirmar = document.getElementById('btnConfirmarIteracion');
    
    const handleConfirmar = () => {
        modal.hide();
        btnConfirmar.removeEventListener('click', handleConfirmar);
        callback(true);
    };

    btnConfirmar.addEventListener('click', handleConfirmar);
    modal.show();

    // Limpiar el evento cuando se cierra el modal
    document.getElementById('modalConfirmacionIteracion').addEventListener('hidden.bs.modal', function () {
        btnConfirmar.removeEventListener('click', handleConfirmar);
        callback(false);
    }, { once: true });
}

// Funciones de utilidad
function actualizarVistaPrevia() {
    const nombre = document.getElementById('nombreTarifa').value;
    const tipo = document.getElementById('tipoHabitacion').options[document.getElementById('tipoHabitacion').selectedIndex].text;
    const precio = document.getElementById('precio').value;
    const temporada = document.getElementById('temporada').value;
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    
    document.getElementById('previewNombre').textContent = nombre || '-';
    document.getElementById('previewTipo').textContent = tipo !== 'Seleccione tipo' ? tipo : '-';
    document.getElementById('previewPrecio').textContent = precio ? precio + ' €' : '0.00 €';
    document.getElementById('previewTemporada').textContent = temporada || 'Temporada';
    document.getElementById('previewInicio').textContent = fechaInicio ? formatearFecha(fechaInicio) : '-';
    document.getElementById('previewFin').textContent = fechaFin ? formatearFecha(fechaFin) : '-';
}

function obtenerFiltros() {
    return {
        tipo: document.getElementById('filtro-tipo').value,
        temporada: document.getElementById('filtro-temporada').value,
        precio_min: document.getElementById('precio-min').value,
        precio_max: document.getElementById('precio-max').value
    };
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES');
}

function mostrarExito(mensaje) {
    Toastify({
        text: mensaje,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#28a745"
    }).showToast();
}

function mostrarError(mensaje) {
    Toastify({
        text: mensaje,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#dc3545"
    }).showToast();
}