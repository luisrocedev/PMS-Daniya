// public/js/clientes.js

// Variables globales
let currentPage = 1;
const itemsPerPage = 10;
let totalItems = 0;

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

function initializeApp() {
    // Cargar datos iniciales
    listarClientesPaginado(1);
    cargarEstadisticasClientes();
    
    // Event listeners
    document.getElementById('formBuscarCliente').addEventListener('submit', (e) => {
        e.preventDefault();
        listarClientesPaginado(1);
    });

    document.getElementById('formCrearCliente').addEventListener('submit', (e) => {
        e.preventDefault();
        crearCliente();
    });

    // Botón crear-tab en header que redirige a la pestaña crear
    document.getElementById('crear-tab').addEventListener('click', (e) => {
        const crearTabNav = document.getElementById('crear-tab-nav');
        const tab = new bootstrap.Tab(crearTabNav);
        tab.show();
    });

    // Validación de DNI en tiempo real
    document.getElementById('dniCli').addEventListener('input', validarDNIEnTiempo);
    document.getElementById('editDni').addEventListener('input', validarDNIEnTiempo);
}

// Función para cargar estadísticas de clientes para las tarjetas
async function cargarEstadisticasClientes() {
    try {
        const response = await fetch('../api/clientes.php?stats=true');
        const data = await response.json();
        
        if (data.success) {
            // Actualizar los contadores en las tarjetas
            document.getElementById('totalClientes').textContent = data.stats.total || 0;
            document.getElementById('clientesNuevos').textContent = data.stats.nuevos || 0;
            document.getElementById('clientesInteresados').textContent = data.stats.interesados || 0;
            document.getElementById('clientesCerrados').textContent = data.stats.cerrados || 0;
        } else {
            console.error('Error al cargar estadísticas:', data.error);
            // Si no hay API específica para stats, calcularemos con todos los clientes
            calcularEstadisticasDeClientes();
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
        // Si hay un error, intentamos calcular las estadísticas de todos los clientes
        calcularEstadisticasDeClientes();
    }
}

// Calcular estadísticas si no hay una API específica
async function calcularEstadisticasDeClientes() {
    try {
        const response = await fetch('../api/clientes.php?limit=1000'); // Obtener todos los clientes
        const data = await response.json();
        
        if (Array.isArray(data.data)) {
            const clientes = data.data;
            const stats = {
                total: clientes.length,
                nuevos: clientes.filter(c => c.estado_funnel === 'Nuevo').length,
                interesados: clientes.filter(c => c.estado_funnel === 'Interesado').length,
                cerrados: clientes.filter(c => c.estado_funnel === 'Cerrado').length
            };
            
            // Actualizar los contadores en las tarjetas
            document.getElementById('totalClientes').textContent = stats.total;
            document.getElementById('clientesNuevos').textContent = stats.nuevos;
            document.getElementById('clientesInteresados').textContent = stats.interesados;
            document.getElementById('clientesCerrados').textContent = stats.cerrados;
        }
    } catch (error) {
        console.error('Error al calcular estadísticas:', error);
    }
}

// Función para listar clientes con paginación y filtros
async function listarClientesPaginado(pagina = 1) {
    currentPage = pagina;
    const search = document.getElementById('searchCli').value;
    const estadoFunnel = document.getElementById('filtroEstado').value;
    
    try {
        let url = `../api/clientes.php?page=${pagina}&limit=${itemsPerPage}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (estadoFunnel) url += `&estado_funnel=${encodeURIComponent(estadoFunnel)}`;

        const response = await fetch(url);
        const data = await response.json();
        
        if (Array.isArray(data.data)) {
            renderizarTablaClientes(data.data);
            renderizarPaginacion(data.total);
            totalItems = data.total;
            
            // Si se realiza una búsqueda y hay resultados, cambiar a la pestaña de listado
            if ((search || estadoFunnel) && data.data.length > 0) {
                const listadoTab = document.querySelector('#listado-tab');
                const tab = new bootstrap.Tab(listadoTab);
                tab.show();
            }
        }
    } catch (error) {
        mostrarMensaje('Error al cargar los clientes', 'error');
        console.error('Error en listarClientesPaginado:', error);
    }
}

// Renderizar tabla de clientes
function renderizarTablaClientes(clientes) {
    const tbody = document.getElementById('tabla-clientes');
    tbody.innerHTML = '';

    if (clientes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron clientes</td></tr>';
        return;
    }

    clientes.forEach(cliente => {
        const tr = document.createElement('tr');
        const estadoClass = cliente.estado_funnel ? 
            `funnel-${cliente.estado_funnel.toLowerCase().replace(' ', '-')}` : '';

        tr.innerHTML = `
            <td>${cliente.id_cliente}</td>
            <td>${cliente.nombre}</td>
            <td>${cliente.apellidos}</td>
            <td>${cliente.dni}</td>
            <td>${cliente.email || ''}</td>
            <td>${cliente.telefono || ''}</td>
            <td><span class="badge ${estadoClass}">${cliente.estado_funnel || 'Sin estado'}</span></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="cliente_detalle_nuevo.php?id=${cliente.id_cliente}" 
                       class="btn btn-info" title="Ver detalles">
                        <i class="fas fa-info-circle"></i>
                    </a>
                    <button class="btn btn-warning" 
                            onclick="abrirModalEditar(${cliente.id_cliente})" 
                            title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger" 
                            onclick="confirmarEliminar(${cliente.id_cliente})"
                            title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Renderizar paginación
function renderizarPaginacion(total) {
    const container = document.getElementById('paginacionClientes');
    const totalPaginas = Math.ceil(total / itemsPerPage);
    
    let html = '<nav aria-label="Paginación de clientes"><ul class="pagination justify-content-center mb-0">';
    
    // Botón Anterior
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarClientesPaginado(${currentPage - 1})" aria-label="Anterior">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); listarClientesPaginado(${i})">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Botón Siguiente
    html += `
        <li class="page-item ${currentPage === totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); listarClientesPaginado(${currentPage + 1})" aria-label="Siguiente">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;
    
    html += '</ul></nav>';
    
    // Añadir información sobre resultados
    html += `<div class="text-muted small mt-2">Mostrando ${total === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1} a ${Math.min(currentPage * itemsPerPage, total)} de ${total} registros</div>`;
    
    container.innerHTML = html;
}

// Crear nuevo cliente
async function crearCliente() {
    const formData = {
        nombre: document.getElementById('nombreCli').value,
        apellidos: document.getElementById('apellidosCli').value,
        dni: document.getElementById('dniCli').value,
        email: document.getElementById('emailCli').value,
        telefono: document.getElementById('telCli').value,
        direccion: document.getElementById('dirCli').value,
        estado_funnel: document.getElementById('estado_funnel').value
    };

    if (!validarDNI(formData.dni)) {
        mostrarMensaje('El DNI introducido no es válido', 'error');
        return;
    }

    try {
        const response = await fetch('../api/clientes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(formData)
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('Cliente creado con éxito', 'success');
            document.getElementById('formCrearCliente').reset();
            listarClientesPaginado(1);
            cargarEstadisticasClientes(); // Actualizar estadísticas
            
            // Cambiar a la pestaña de listado
            const listadoTab = document.querySelector('#listado-tab');
            const tab = new bootstrap.Tab(listadoTab);
            tab.show();
        } else {
            throw new Error(data.error || 'Error al crear el cliente');
        }
    } catch (error) {
        mostrarMensaje(error.message, 'error');
        console.error('Error en crearCliente:', error);
    }
}

// Abrir modal de edición
async function abrirModalEditar(idCliente) {
    try {
        const response = await fetch(`../api/clientes.php?id=${idCliente}`);
        const cliente = await response.json();
        
        if (cliente) {
            document.getElementById('editId').value = cliente.id_cliente;
            document.getElementById('editNombre').value = cliente.nombre;
            document.getElementById('editApellidos').value = cliente.apellidos;
            document.getElementById('editDni').value = cliente.dni;
            document.getElementById('editEmail').value = cliente.email || '';
            document.getElementById('editTelefono').value = cliente.telefono || '';
            
            // Verificar si existe el campo dirección en el modal
            const direccionField = document.getElementById('editDireccion');
            if (direccionField && cliente.direccion) {
                direccionField.value = cliente.direccion;
            }
            
            document.getElementById('editEstadoFunnel').value = cliente.estado_funnel || '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
            modal.show();
        }
    } catch (error) {
        mostrarMensaje('Error al cargar los datos del cliente', 'error');
        console.error('Error en abrirModalEditar:', error);
    }
}

// Actualizar cliente
async function actualizarCliente() {
    const id = document.getElementById('editId').value;
    const formData = {
        nombre: document.getElementById('editNombre').value,
        apellidos: document.getElementById('editApellidos').value,
        dni: document.getElementById('editDni').value,
        email: document.getElementById('editEmail').value,
        telefono: document.getElementById('editTelefono').value,
        estado_funnel: document.getElementById('editEstadoFunnel').value
    };

    // Añadir dirección si existe el campo
    const direccionField = document.getElementById('editDireccion');
    if (direccionField) {
        formData.direccion = direccionField.value;
    }

    if (!validarDNI(formData.dni)) {
        mostrarMensaje('El DNI introducido no es válido', 'error');
        return;
    }

    try {
        const response = await fetch(`../api/clientes.php?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(formData)
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('Cliente actualizado con éxito', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente')).hide();
            listarClientesPaginado(currentPage);
            cargarEstadisticasClientes(); // Actualizar estadísticas
        } else {
            throw new Error(data.error || 'Error al actualizar el cliente');
        }
    } catch (error) {
        mostrarMensaje(error.message, 'error');
        console.error('Error en actualizarCliente:', error);
    }
}

// Confirmar eliminación
function confirmarEliminar(idCliente) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminarCliente(idCliente);
        }
    });
}

// Eliminar cliente
async function eliminarCliente(idCliente) {
    try {
        const response = await fetch(`../api/clientes.php?id=${idCliente}`, {
            method: 'DELETE'
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('Cliente eliminado con éxito', 'success');
            
            // Si es el único elemento de la página actual y no es la primera página
            if (totalItems % itemsPerPage === 1 && currentPage > 1) {
                listarClientesPaginado(currentPage - 1);
            } else {
                listarClientesPaginado(currentPage);
            }
            
            cargarEstadisticasClientes(); // Actualizar estadísticas
        } else {
            throw new Error(data.error || 'Error al eliminar el cliente');
        }
    } catch (error) {
        mostrarMensaje(error.message, 'error');
        console.error('Error en eliminarCliente:', error);
    }
}

// Exportar clientes
function exportarClientes(formato) {
    const search = document.getElementById('searchCli').value;
    const estadoFunnel = document.getElementById('filtroEstado').value;
    
    let url = `../api/exportar_clientes.php?formato=${formato}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (estadoFunnel) url += `&estado_funnel=${encodeURIComponent(estadoFunnel)}`;
    
    window.open(url, '_blank');
}

// Validación de DNI
function validarDNI(dni) {
    const letras = "TRWAGMYFPDXBNJZSQVHLCKE";
    const regex = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;

    if (!regex.test(dni)) return false;

    const numero = dni.substr(0, 8);
    const letra = dni.substr(8, 1).toUpperCase();
    const letraCalculada = letras.charAt(parseInt(numero) % 23);

    return letra === letraCalculada;
}

// Validación de DNI en tiempo real
function validarDNIEnTiempo(event) {
    const input = event.target;
    const dni = input.value.toUpperCase();
    
    if (dni.length === 9) {
        if (!validarDNI(dni)) {
            input.classList.add('is-invalid');
            if (!input.nextElementSibling?.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'El DNI introducido no es válido';
                input.parentNode.appendChild(feedback);
            }
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            const feedback = input.nextElementSibling;
            if (feedback?.classList.contains('invalid-feedback')) {
                feedback.remove();
            }
        }
    } else {
        input.classList.remove('is-invalid', 'is-valid');
        const feedback = input.nextElementSibling;
        if (feedback?.classList.contains('invalid-feedback')) {
            feedback.remove();
        }
    }
}

// Mostrar mensajes con SweetAlert2
function mostrarMensaje(mensaje, tipo) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: tipo === 'error' ? 'error' : 'success',
        title: mensaje
    });
}
