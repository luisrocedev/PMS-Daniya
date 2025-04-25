// Variables globales
let limitePorPagina = 10;
let tarifaEditando = null;

document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

function initializeApp() {
    listarTarifasPaginado(1);
    cargarTiposHabitacion();
    cargarTemporadas();
    
    // Inicializar campos de fecha
    const hoy = new Date();
    document.getElementById('fecha_inicio').valueAsDate = hoy;
    document.getElementById('fecha_fin').valueAsDate = new Date(hoy.getTime() + (90 * 24 * 60 * 60 * 1000)); // +90 días por defecto
    
    // Inicializar tooltips y popovers
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(t => new bootstrap.Tooltip(t));
}

// Función para listar tarifas con paginación y filtros
function listarTarifasPaginado(pagina = 1) {
    const tipoHab = document.getElementById('filtroTipoHab')?.value || '';
    const temporada = document.getElementById('filtroTemporada')?.value || '';
    const fechaInicio = document.getElementById('filtroFechaInicio')?.value || '';
    const fechaFin = document.getElementById('filtroFechaFin')?.value || '';

    let url = `../api/tarifas.php?page=${pagina}&limit=${limitePorPagina}`;
    if (tipoHab) url += `&tipo_hab=${encodeURIComponent(tipoHab)}`;
    if (temporada) url += `&temporada=${encodeURIComponent(temporada)}`;
    if (fechaInicio) url += `&fecha_inicio=${encodeURIComponent(fechaInicio)}`;
    if (fechaFin) url += `&fecha_fin=${encodeURIComponent(fechaFin)}`;

    fetch(url)
        .then(r => r.json())
        .then(obj => {
            const data = obj.data || [];
            const total = obj.total || 0;
            const paginaActual = obj.page || 1;
            
            renderTablaTarifas(data);
            renderPaginacion(paginaActual, limitePorPagina, total);
            actualizarEstadisticas();
        })
        .catch(error => {
            console.error('Error al listar tarifas:', error);
            mostrarAlerta('Error al cargar las tarifas', 'danger');
        });
}

// Renderizar tabla de tarifas
function renderTablaTarifas(tarifas) {
    const tbody = document.getElementById('tabla-tarifas');
    if (!tbody) return;

    tbody.innerHTML = '';
    tarifas.forEach(t => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${t.id_tarifa}</td>
            <td>${t.nombre_tarifa}</td>
            <td>${t.tipo_habitacion}</td>
            <td class="text-end">${formatearMoneda(t.precio)}</td>
            <td><span class="badge bg-${getTemporadaColor(t.temporada)}">${t.temporada}</span></td>
            <td>${formatearFecha(t.fecha_inicio)}</td>
            <td>${formatearFecha(t.fecha_fin)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-info" onclick="verDetalleTarifa(${t.id_tarifa})" title="Ver Detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-primary" onclick="editarTarifa(${t.id_tarifa})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger" onclick="confirmarEliminar(${t.id_tarifa})" title="Eliminar">
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
    const divPag = document.getElementById('paginacionTarifas');
    if (!divPag) return;

    const totalPaginas = Math.ceil(total / limite);
    let html = '<nav aria-label="Navegación de tarifas"><ul class="pagination justify-content-center">';
    
    // Botón anterior
    html += `
        <li class="page-item ${pagina <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarTarifasPaginado(${pagina - 1})" tabindex="-1">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;

    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        html += `
            <li class="page-item ${pagina === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); listarTarifasPaginado(${i})">${i}</a>
            </li>
        `;
    }

    // Botón siguiente
    html += `
        <li class="page-item ${pagina >= totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarTarifasPaginado(${pagina + 1})">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;

    html += '</ul></nav>';
    divPag.innerHTML = html;
}

// Crear/Editar tarifa
function guardarTarifa(e) {
    e.preventDefault();
    
    const formData = {
        nombre_tarifa: document.getElementById('nombreTarifa').value,
        tipo_habitacion: document.getElementById('tipoHabitacion').value,
        precio: document.getElementById('precio').value,
        temporada: document.getElementById('temporada').value,
        fecha_inicio: document.getElementById('fecha_inicio').value,
        fecha_fin: document.getElementById('fecha_fin').value
    };

    const method = tarifaEditando ? 'PUT' : 'POST';
    const url = tarifaEditando 
        ? `../api/tarifas.php?id=${tarifaEditando}`
        : '../api/tarifas.php';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta(`Tarifa ${tarifaEditando ? 'actualizada' : 'creada'} con éxito`, 'success');
            document.getElementById('formTarifa').reset();
            document.getElementById('fecha_inicio').valueAsDate = new Date();
            document.getElementById('fecha_fin').valueAsDate = new Date(Date.now() + (90 * 24 * 60 * 60 * 1000));
            listarTarifasPaginado(1);
            cerrarModalTarifa();
            tarifaEditando = null;
        } else {
            mostrarAlerta(data.error || `Error al ${tarifaEditando ? 'actualizar' : 'crear'} la tarifa`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error al guardar tarifa:', error);
        mostrarAlerta('Error al procesar la solicitud', 'danger');
    });
}

// Editar tarifa
function editarTarifa(id) {
    fetch(`../api/tarifas.php?id=${id}`)
        .then(r => r.json())
        .then(tarifa => {
            if (!tarifa.id_tarifa) {
                mostrarAlerta('No se encontró la tarifa', 'danger');
                return;
            }
            
            tarifaEditando = tarifa.id_tarifa;
            document.getElementById('nombreTarifa').value = tarifa.nombre_tarifa;
            document.getElementById('tipoHabitacion').value = tarifa.tipo_habitacion;
            document.getElementById('precio').value = tarifa.precio;
            document.getElementById('temporada').value = tarifa.temporada;
            document.getElementById('fecha_inicio').value = tarifa.fecha_inicio;
            document.getElementById('fecha_fin').value = tarifa.fecha_fin;
            
            document.getElementById('modalTarifaLabel').textContent = 'Editar Tarifa';
            const modal = new bootstrap.Modal(document.getElementById('modalTarifa'));
            modal.show();
        })
        .catch(error => {
            console.error('Error al cargar tarifa:', error);
            mostrarAlerta('Error al cargar los detalles de la tarifa', 'danger');
        });
}

// Ver detalle de tarifa
function verDetalleTarifa(id) {
    fetch(`../api/tarifas.php?id=${id}`)
        .then(r => r.json())
        .then(tarifa => {
            if (!tarifa.id_tarifa) {
                mostrarAlerta('No se encontró la tarifa', 'danger');
                return;
            }
            
            document.getElementById('detalleTarifaId').textContent = tarifa.id_tarifa;
            document.getElementById('detalleTarifaNombre').textContent = tarifa.nombre_tarifa;
            document.getElementById('detalleTarifaTipo').textContent = tarifa.tipo_habitacion;
            document.getElementById('detalleTarifaPrecio').textContent = formatearMoneda(tarifa.precio);
            document.getElementById('detalleTarifaTemporada').textContent = tarifa.temporada;
            document.getElementById('detalleTarifaInicio').textContent = formatearFecha(tarifa.fecha_inicio);
            document.getElementById('detalleTarifaFin').textContent = formatearFecha(tarifa.fecha_fin);
            
            const modal = new bootstrap.Modal(document.getElementById('modalDetalleTarifa'));
            modal.show();
        })
        .catch(error => {
            console.error('Error al cargar detalle de tarifa:', error);
            mostrarAlerta('Error al cargar los detalles de la tarifa', 'danger');
        });
}

// Eliminar tarifa
function confirmarEliminar(id) {
    if (confirm('¿Está seguro de que desea eliminar esta tarifa? Esta acción no se puede deshacer.')) {
        fetch(`../api/tarifas.php?id=${id}`, { method: 'DELETE' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Tarifa eliminada con éxito', 'success');
                    listarTarifasPaginado(1);
                } else {
                    mostrarAlerta(data.error || 'Error al eliminar la tarifa', 'danger');
                }
            })
            .catch(error => {
                console.error('Error al eliminar tarifa:', error);
                mostrarAlerta('Error al eliminar la tarifa', 'danger');
            });
    }
}

// Cargar selectores dinámicos
function cargarTiposHabitacion() {
    fetch('../api/habitaciones.php?tipos=true')
        .then(r => r.json())
        .then(tipos => {
            const selectTipo = document.getElementById('tipoHabitacion');
            const selectFiltro = document.getElementById('filtroTipoHab');
            
            const options = tipos.map(t => 
                `<option value="${t.tipo}">${t.tipo}</option>`
            ).join('');
            
            if (selectTipo) {
                selectTipo.innerHTML = '<option value="">Seleccione tipo</option>' + options;
            }
            if (selectFiltro) {
                selectFiltro.innerHTML = '<option value="">Todos los tipos</option>' + options;
            }
        });
}

function cargarTemporadas() {
    const temporadas = ['Alta', 'Media', 'Baja'];
    const selectTemp = document.getElementById('temporada');
    const selectFiltro = document.getElementById('filtroTemporada');
    
    const options = temporadas.map(t => 
        `<option value="${t}">${t}</option>`
    ).join('');
    
    if (selectTemp) {
        selectTemp.innerHTML = '<option value="">Seleccione temporada</option>' + options;
    }
    if (selectFiltro) {
        selectFiltro.innerHTML = '<option value="">Todas las temporadas</option>' + options;
    }
}

// Funciones auxiliares
function getTemporadaColor(temporada) {
    const colores = {
        'Alta': 'danger',
        'Media': 'warning',
        'Baja': 'success'
    };
    return colores[temporada] || 'secondary';
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(valor);
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
function cerrarModalTarifa() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalTarifa'));
    if (modal) modal.hide();
}

function prepararNuevaTarifa() {
    tarifaEditando = null;
    document.getElementById('formTarifa').reset();
    document.getElementById('fecha_inicio').valueAsDate = new Date();
    document.getElementById('fecha_fin').valueAsDate = new Date(Date.now() + (90 * 24 * 60 * 60 * 1000));
    document.getElementById('modalTarifaLabel').textContent = 'Nueva Tarifa';
}

function actualizarEstadisticas() {
    fetch('../api/tarifas.php?stats=true')
        .then(r => r.json())
        .then(stats => {
            document.getElementById('totalTarifas').textContent = stats.total;
            document.getElementById('tarifaPromedio').textContent = formatearMoneda(stats.promedio);
            document.getElementById('tarifasActivas').textContent = stats.activas;
            document.getElementById('tarifasVencer').textContent = stats.por_vencer;
        })
        .catch(error => {
            console.error('Error al cargar estadísticas:', error);
            mostrarAlerta('Error al cargar estadísticas', 'danger');
        });
}