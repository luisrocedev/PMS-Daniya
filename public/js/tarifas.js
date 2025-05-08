let currentPage = 1;
const itemsPerPage = 10;

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    cargarTiposHabitacion();
    cargarEstadisticas();
    cargarTarifas(1);
    cargarFiltrosTarifas();
    
    // Listeners para vista previa en tiempo real
    const nombreTarifa = document.getElementById('nombreTarifa');
    if (nombreTarifa) {
        nombreTarifa.addEventListener('input', actualizarVistaPrevia);
    }
    const tipoHabitacion = document.getElementById('tipoHabitacion');
    if (tipoHabitacion) {
        tipoHabitacion.addEventListener('change', actualizarVistaPrevia);
    }
    const precio = document.getElementById('precio');
    if (precio) {
        precio.addEventListener('input', actualizarVistaPrevia);
    }
    const temporada = document.getElementById('temporada');
    if (temporada) {
        temporada.addEventListener('change', actualizarVistaPrevia);
    }
    const fechaInicio = document.getElementById('fecha_inicio');
    if (fechaInicio) {
        fechaInicio.addEventListener('change', actualizarVistaPrevia);
    }
    const fechaFin = document.getElementById('fecha_fin');
    if (fechaFin) {
        fechaFin.addEventListener('change', actualizarVistaPrevia);
    }

    // Listener para filtros
    const formFiltros = document.getElementById('form-filtros');
    if (formFiltros) {
        formFiltros.addEventListener('submit', (e) => {
            e.preventDefault();
            cargarTarifas(1);
        });
    }

    // Solo añade event listeners si los elementos existen
    const filtro = document.getElementById('filtroEstado');
    if (filtro) {
        filtro.addEventListener('change', () => cargarTarifas(1));
    }

    cargarTarifas(1);
});

// Funciones de carga de datos
async function cargarTiposHabitacion() {
    try {
        const response = await fetch('../api/tipos_habitacion.php');
        const data = await response.json();
        
        if (data.success) {
            const selectTipos = document.getElementById('tipoHabitacion');
            const selectFiltro = document.getElementById('filtro-tipo');
            
            if (selectTipos && selectFiltro) {
                data.data.forEach(tipo => {
                    selectTipos.innerHTML += `<option value="${tipo.nombre}">${tipo.nombre}</option>`;
                    selectFiltro.innerHTML += `<option value="${tipo.nombre}">${tipo.nombre}</option>`;
                });
            }
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
            const totalTarifas = document.getElementById('total-tarifas');
            const tarifaPromedio = document.getElementById('tarifa-promedio');
            const tarifasActivas = document.getElementById('tarifas-activas');
            const tiposHabitacion = document.getElementById('tipos-habitacion');

            if (totalTarifas) totalTarifas.textContent = data.stats.total;
            if (tarifaPromedio) tarifaPromedio.textContent = data.stats.promedio + '€';
            if (tarifasActivas) tarifasActivas.textContent = data.stats.activas;
            if (tiposHabitacion) tiposHabitacion.textContent = data.stats.tipos;
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

// Cargar tipos de habitación y temporadas para los filtros de búsqueda
async function cargarFiltrosTarifas() {
    // Tipos de habitación
    try {
        const respTipos = await fetch('../api/tipos_habitacion.php');
        const dataTipos = await respTipos.json();
        const selectFiltroTipo = document.getElementById('filtroTipoHab');
        if (selectFiltroTipo && dataTipos.success && Array.isArray(dataTipos.data)) {
            selectFiltroTipo.innerHTML = '<option value="">Todos los tipos</option>' +
                dataTipos.data.map(tipo => `<option value="${tipo.nombre}">${tipo.nombre}</option>`).join('');
        }
    } catch (e) {
        // No mostrar error, solo dejar el select vacío
    }
    // Temporadas
    try {
        const respTemps = await fetch('../api/tarifas.php');
        const dataTemps = await respTemps.json();
        const selectFiltroTemp = document.getElementById('filtroTemporada');
        if (selectFiltroTemp && dataTemps.success && Array.isArray(dataTemps.data)) {
            // Obtener temporadas únicas
            const temporadas = [...new Set(dataTemps.data.map(t => t.temporada))];
            selectFiltroTemp.innerHTML = '<option value="">Todas las temporadas</option>' +
                temporadas.map(temp => `<option value="${temp}">${temp}</option>`).join('');
        }
    } catch (e) {
        // No mostrar error
    }
}

// Funciones de renderizado
function renderizarTarifas(tarifas) {
    const tbody = document.getElementById('tabla-tarifas');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    tarifas.forEach(tarifa => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${tarifa.id_tarifa}</td>
            <td>${tarifa.nombre_tarifa}</td>
            <td>${tarifa.tipo_habitacion}</td>
            <td class="text-end">${parseFloat(tarifa.precio).toFixed(2)}€</td>
            <td>${tarifa.temporada}</td>
            <td>${formatearFecha(tarifa.fecha_inicio)}</td>
            <td>${formatearFecha(tarifa.fecha_fin)}</td>
            <td>
                <button class="btn btn-sm btn-info me-1" onclick="verDetalleTarifa(${tarifa.id_tarifa})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-primary me-1" onclick="editarTarifa(${tarifa.id_tarifa})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="eliminarTarifa(${tarifa.id_tarifa})">
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
    if (!paginacion) return;

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
        nombre: document.getElementById('nombreTarifa')?.value || '',
        tipo_habitacion_id: document.getElementById('tipoHabitacion')?.value || '',
        precio: document.getElementById('precio')?.value || '',
        temporada: document.getElementById('temporada')?.value || '',
        fecha_inicio: document.getElementById('fecha_inicio')?.value || '',
        fecha_fin: document.getElementById('fecha_fin')?.value || ''
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
        // Asegurarse de que el select de tipo de habitación esté cargado
        if (typeof cargarTiposHabitacion === 'function') {
            await cargarTiposHabitacion();
        }
        const response = await fetch(`../api/tarifas.php?id=${id}`);
        const data = await response.json();
        if (data.success) {
            const tarifa = data.data;
            const nombreTarifa = document.getElementById('nombreTarifa');
            const tipoHabitacion = document.getElementById('tipoHabitacion');
            const precio = document.getElementById('precio');
            const temporada = document.getElementById('temporada');
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');

            if (nombreTarifa) nombreTarifa.value = tarifa.nombre_tarifa || '';
            if (tipoHabitacion) tipoHabitacion.value = tarifa.tipo_habitacion || '';
            if (precio) precio.value = tarifa.precio || '';
            if (temporada) temporada.value = tarifa.temporada || '';
            if (fechaInicio) fechaInicio.value = tarifa.fecha_inicio || '';
            if (fechaFin) fechaFin.value = tarifa.fecha_fin || '';

            const modalLabel = document.getElementById('modalTarifaLabel');
            if (modalLabel) modalLabel.textContent = 'Editar Tarifa';
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
            const detalleTarifaId = document.getElementById('detalleTarifaId');
            const detalleTarifaNombre = document.getElementById('detalleTarifaNombre');
            const detalleTarifaTipo = document.getElementById('detalleTarifaTipo');
            const detalleTarifaPrecio = document.getElementById('detalleTarifaPrecio');
            const detalleTarifaTemporada = document.getElementById('detalleTarifaTemporada');
            const detalleTarifaInicio = document.getElementById('detalleTarifaInicio');
            const detalleTarifaFin = document.getElementById('detalleTarifaFin');

            if (detalleTarifaId) detalleTarifaId.textContent = tarifa.id;
            if (detalleTarifaNombre) detalleTarifaNombre.textContent = tarifa.nombre;
            if (detalleTarifaTipo) detalleTarifaTipo.textContent = tarifa.tipo_habitacion;
            if (detalleTarifaPrecio) detalleTarifaPrecio.textContent = tarifa.precio + '€';
            if (detalleTarifaTemporada) detalleTarifaTemporada.textContent = tarifa.temporada;
            if (detalleTarifaInicio) detalleTarifaInicio.textContent = formatearFecha(tarifa.fecha_inicio);
            if (detalleTarifaFin) detalleTarifaFin.textContent = formatearFecha(tarifa.fecha_fin);
            
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
    const nombre = document.getElementById('nombreTarifa')?.value || '-';
    const tipo = document.getElementById('tipoHabitacion')?.options[document.getElementById('tipoHabitacion')?.selectedIndex]?.text || '-';
    const precio = document.getElementById('precio')?.value || '0.00';
    const temporada = document.getElementById('temporada')?.value || 'Temporada';
    const fechaInicio = document.getElementById('fecha_inicio')?.value || '-';
    const fechaFin = document.getElementById('fecha_fin')?.value || '-';
    
    const previewNombre = document.getElementById('previewNombre');
    const previewTipo = document.getElementById('previewTipo');
    const previewPrecio = document.getElementById('previewPrecio');
    const previewTemporada = document.getElementById('previewTemporada');
    const previewInicio = document.getElementById('previewInicio');
    const previewFin = document.getElementById('previewFin');

    if (previewNombre) previewNombre.textContent = nombre;
    if (previewTipo) previewTipo.textContent = tipo !== 'Seleccione tipo' ? tipo : '-';
    if (previewPrecio) previewPrecio.textContent = precio + ' €';
    if (previewTemporada) previewTemporada.textContent = temporada;
    if (previewInicio) previewInicio.textContent = fechaInicio;
    if (previewFin) previewFin.textContent = fechaFin;
}

function obtenerFiltros() {
    // Protege el acceso a los elementos
    const tipo = document.getElementById('filtroTipo');
    const estado = document.getElementById('filtroEstado');
    return {
        tipo: tipo ? tipo.value : '',
        estado: estado ? estado.value : ''
    };
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES');
}

function mostrarExito(mensaje) {
    alert(mensaje);
}

function mostrarError(mensaje) {
    alert(mensaje);
}