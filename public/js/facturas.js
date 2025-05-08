// facturas.js
// Variables globales
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

const limitePorPagina = 10;

function initializeApp() {
    listarFacturasPaginado(1);
    cargarSelectReservas();
    
    // Inicializar campos de fecha de forma segura
    const inputFecha = document.getElementById('fechaF');
    if (inputFecha) {
        inputFecha.valueAsDate = new Date();
    }
    
    // Auto-actualización cada 5 minutos
    setInterval(() => {
        listarFacturasPaginado(1);
        actualizarEstadisticas();
    }, 300000);
}

// Función para listar facturas con paginación y filtros
function listarFacturasPaginado(pagina = 1) {
    const reserva = document.getElementById('reservaF')?.value || '';
    const fechaInicio = document.getElementById('fechaInicio')?.value || '';
    const fechaFin = document.getElementById('fechaFin')?.value || '';

    let url = `../api/facturas.php?page=${pagina}&limit=${limitePorPagina}`;
    if (reserva) url += `&reserva=${encodeURIComponent(reserva)}`;
    if (fechaInicio) url += `&fecha_inicio=${encodeURIComponent(fechaInicio)}`;
    if (fechaFin) url += `&fecha_fin=${encodeURIComponent(fechaFin)}`;

    fetch(url)
        .then(r => r.json())
        .then(obj => {
            const data = obj.data || [];
            const total = obj.total || 0;
            const paginaActual = obj.page || 1;
            
            renderTablaFacturas(data);
            renderPaginacion(paginaActual, limitePorPagina, total);
            actualizarEstadisticas();
        })
        .catch(error => {
            console.error('Error al listar facturas:', error);
            mostrarAlerta('Error al cargar las facturas', 'danger');
        });
}

// Renderizar tabla de facturas
function renderTablaFacturas(facturas) {
    const tbody = document.getElementById('tabla-fact');
    if (!tbody) return;

    tbody.innerHTML = '';
    facturas.forEach(f => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${f.id_factura}</td>
            <td>${f.id_reserva}</td>
            <td>${formatearFecha(f.fecha_emision)}</td>
            <td class="text-end">${formatearMoneda(f.total)}</td>
            <td><span class="badge bg-${getMetodoPagoColor(f.metodo_pago)}">${f.metodo_pago}</span></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-info" onclick="verDetalleFactura(${f.id_factura})" title="Ver Detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-primary" onclick="imprimirFactura(${f.id_factura})" title="Imprimir">
                        <i class="fas fa-print"></i>
                    </button>
                    <button class="btn btn-danger" onclick="confirmarEliminar(${f.id_factura})" title="Eliminar">
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
    const divPag = document.getElementById('paginacionFact');
    if (!divPag) return;

    const totalPaginas = Math.ceil(total / limite);
    let html = '<nav aria-label="Navegación de facturas"><ul class="pagination justify-content-center">';
    
    // Botón anterior
    html += `
        <li class="page-item ${pagina <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarFacturasPaginado(${pagina - 1})" tabindex="-1">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;

    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        html += `
            <li class="page-item ${pagina === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); listarFacturasPaginado(${i})">${i}</a>
            </li>
        `;
    }

    // Botón siguiente
    html += `
        <li class="page-item ${pagina >= totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarFacturasPaginado(${pagina + 1})">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;

    html += '</ul></nav>';
    divPag.innerHTML = html;
}

// Crear nueva factura
function crearFactura(e) {
    e.preventDefault();
    
    const formData = {
        id_reserva: document.getElementById('idResF').value,
        fecha_emision: document.getElementById('fechaF').value,
        total: document.getElementById('totalF').value,
        metodo_pago: document.getElementById('metodoF').value
    };

    fetch('../api/facturas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Factura creada con éxito', 'success');
            document.getElementById('formCrearFactura').reset();
            document.getElementById('fechaF').valueAsDate = new Date();
            listarFacturasPaginado(1);
            cerrarModalCrear();
        } else {
            mostrarAlerta(data.error || 'Error al crear la factura', 'danger');
        }
    })
    .catch(error => {
        console.error('Error al crear factura:', error);
        mostrarAlerta('Error al crear la factura', 'danger');
    });
}

// Eliminar factura
function confirmarEliminar(id) {
    if (confirm('¿Está seguro de que desea eliminar esta factura? Esta acción no se puede deshacer.')) {
        fetch(`../api/facturas.php?id=${id}`, { method: 'DELETE' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Factura eliminada con éxito', 'success');
                    listarFacturasPaginado(1);
                } else {
                    mostrarAlerta(data.error || 'Error al eliminar la factura', 'danger');
                }
            })
            .catch(error => {
                console.error('Error al eliminar factura:', error);
                mostrarAlerta('Error al eliminar la factura', 'danger');
            });
    }
}

// Ver detalle de factura
function verDetalleFactura(id) {
    fetch(`../api/facturas.php?id=${id}`)
        .then(r => r.json())
        .then(factura => {
            if (!factura.id_factura) {
                mostrarAlerta('No se encontró la factura', 'danger');
                return;
            }
            
            // Llenar modal con detalles
            document.getElementById('detalleFacturaId').textContent = factura.id_factura;
            document.getElementById('detalleReservaId').textContent = factura.id_reserva;
            document.getElementById('detalleFechaEmision').textContent = formatearFecha(factura.fecha_emision);
            document.getElementById('detalleTotal').textContent = formatearMoneda(factura.total);
            document.getElementById('detalleMetodoPago').textContent = factura.metodo_pago;
            
            const modal = new bootstrap.Modal(document.getElementById('modalDetalleFactura'));
            modal.show();
        })
        .catch(error => {
            console.error('Error al cargar detalle de factura:', error);
            mostrarAlerta('Error al cargar los detalles de la factura', 'danger');
        });
}

// Imprimir factura
function imprimirFactura(id) {
    window.open(`generar_factura.php?id=${id}`, '_blank');
}

// Cargar selects dinámicos
function cargarSelectReservas() {
    fetch('../api/reservas.php')
        .then(r => r.json())
        .then(data => {
            const reservas = Array.isArray(data) ? data : [];
            const selectRes = document.getElementById('idResF');
            const selectResFiltro = document.getElementById('reservaF');
            
            const options = reservas.map(r => 
                `<option value="${r.id_reserva}">Reserva #${r.id_reserva}</option>`
            ).join('');
            
            if (selectRes) selectRes.innerHTML = '<option value="">Seleccione una reserva</option>' + options;
            if (selectResFiltro) selectResFiltro.innerHTML = '<option value="">Todas las reservas</option>' + options;
        });
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    fetch('../api/facturas.php?stats=true')
        .then(r => r.json())
        .then(stats => {
            if (document.getElementById('totalFacturas'))
                document.getElementById('totalFacturas').textContent = stats.total || 0;
            if (document.getElementById('totalImporte'))
                document.getElementById('totalImporte').textContent = formatearMoneda(stats.importe_total || 0);
            if (document.getElementById('promedioFactura'))
                document.getElementById('promedioFactura').textContent = formatearMoneda(stats.promedio || 0);
            if (document.getElementById('facturasPendientes'))
                document.getElementById('facturasPendientes').textContent = stats.pendientes || 0;
        })
        .catch(error => console.error('Error al actualizar estadísticas:', error));
}

// Funciones auxiliares
function getMetodoPagoColor(metodo) {
    const colores = {
        'Efectivo': 'success',
        'Tarjeta': 'primary',
        'Transferencia': 'info'
    };
    return colores[metodo] || 'secondary';
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
function cerrarModalCrear() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaFactura'));
    if (modal) modal.hide();
}
