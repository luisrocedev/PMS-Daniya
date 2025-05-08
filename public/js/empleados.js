// empleados.js
document.addEventListener('DOMContentLoaded', () => {
  inicializarEmpleados();
});

function inicializarEmpleados() {
  // Cargar datos iniciales
  listarEmpleados();
  cargarEstadisticasEmpleados();
  cargarRolesYDeps();

  // Event Listeners
  document.getElementById('btnNuevoEmpleado').addEventListener('click', () => {
    resetearFormulario();
    document.getElementById('modalEmpleadoTitulo').textContent = 'Nuevo Empleado';
    const modal = new bootstrap.Modal(document.getElementById('modalEmpleado'));
    modal.show();
  });

  document.getElementById('btnGuardarEmpleado').addEventListener('click', () => {
    const idEmpleado = document.getElementById('id_empleado').value;
    if (idEmpleado) {
      actualizarEmpleado(idEmpleado);
    } else {
      crearEmpleado();
    }
  });
}

// Cargar estadísticas de empleados
async function cargarEstadisticasEmpleados() {
  try {
    const response = await fetch('../api/empleados.php?stats=true');
    const data = await response.json();

    if (data.stats) {
      // Actualizar los contadores en las tarjetas
      document.getElementById('totalEmpleados').textContent = data.stats.total || 0;
      document.getElementById('totalDirectivos').textContent = data.stats.directivos || 0;
      document.getElementById('empleadosActivos').textContent = data.stats.activos || 0;
      document.getElementById('departamentos').textContent = data.stats.departamentos || 0;
    } else {
      // Si no hay una respuesta específica con stats, calcular basándose en los datos generales
      calcularEstadisticasEmpleados();
    }
  } catch (error) {
    console.error('Error al cargar estadísticas:', error);
    // En caso de error, intentar calcular estadísticas
    calcularEstadisticasEmpleados();
  }
}

// Calcular estadísticas si no hay endpoint específico para ello
async function calcularEstadisticasEmpleados() {
  try {
    // Obtener todos los empleados
    const responseEmpleados = await fetch('../api/empleados.php');
    const dataEmpleados = await responseEmpleados.json();
    const empleados = Array.isArray(dataEmpleados) ? dataEmpleados : (dataEmpleados.data || []);

    // Obtener departamentos
    const responseDeps = await fetch('../api/departamentos.php');
    const dataDeps = await responseDeps.json();
    const departamentos = Array.isArray(dataDeps) ? dataDeps : (dataDeps.data || []);

    // Calcular estadísticas
    const totalEmpleados = empleados.length;
    const directivos = empleados.filter(emp => emp.id_rol === 1 || emp.id_rol === '1').length;
    
    // Actualizar las tarjetas
    document.getElementById('totalEmpleados').textContent = totalEmpleados;
    document.getElementById('totalDirectivos').textContent = directivos;
    document.getElementById('empleadosActivos').textContent = totalEmpleados; // Suponemos que todos están activos
    document.getElementById('departamentos').textContent = departamentos.length || 0;
  } catch (error) {
    console.error('Error al calcular estadísticas:', error);
  }
}

// Cargar roles y departamentos para los selectores
async function cargarRolesYDeps() {
  try {
    // Cargar roles
    const responseRoles = await fetch('../api/roles.php');
    const dataRoles = await responseRoles.json();
    const roles = Array.isArray(dataRoles) ? dataRoles : (dataRoles.data || []);

    // Cargar departamentos
    const responseDeps = await fetch('../api/departamentos.php');
    const dataDeps = await responseDeps.json();
    const departamentos = Array.isArray(dataDeps) ? dataDeps : (dataDeps.data || []);

    // Llenar selector de roles
    const selectorRol = document.getElementById('id_rol');
    const selectorRolBusqueda = document.getElementById('buscarRol');
    
    if (selectorRol) {
      selectorRol.innerHTML = '<option value="" disabled selected>Seleccione rol</option>';
      roles.forEach(rol => {
        const option = document.createElement('option');
        option.value = rol.id_rol;
        option.textContent = rol.nombre_rol;
        selectorRol.appendChild(option);
      });
    }

    if (selectorRolBusqueda) {
      selectorRolBusqueda.innerHTML = '<option value="">Todos</option>';
      roles.forEach(rol => {
        const option = document.createElement('option');
        option.value = rol.id_rol;
        option.textContent = rol.nombre_rol;
        selectorRolBusqueda.appendChild(option);
      });
    }

    // Llenar selector de departamentos
    const selectorDep = document.getElementById('id_departamento');
    const selectorDepBusqueda = document.getElementById('buscarDep');
    
    if (selectorDep) {
      selectorDep.innerHTML = '<option value="" disabled selected>Seleccione departamento</option>';
      departamentos.forEach(dep => {
        const option = document.createElement('option');
        option.value = dep.id_departamento;
        option.textContent = dep.nombre_departamento;
        selectorDep.appendChild(option);
      });
    }

    if (selectorDepBusqueda) {
      selectorDepBusqueda.innerHTML = '<option value="">Todos</option>';
      departamentos.forEach(dep => {
        const option = document.createElement('option');
        option.value = dep.id_departamento;
        option.textContent = dep.nombre_departamento;
        selectorDepBusqueda.appendChild(option);
      });
    }
  } catch (error) {
    console.error('Error al cargar roles y departamentos:', error);
  }
}

// Función para listar todos los empleados (sin paginación)
async function listarEmpleados() {
  try {
    const buscarTxt = document.getElementById('buscarTxt')?.value || '';
    const buscarRol = document.getElementById('buscarRol')?.value || '';
    const buscarDep = document.getElementById('buscarDep')?.value || '';

    let url = '../api/empleados.php?';
    if (buscarTxt) url += `&search=${encodeURIComponent(buscarTxt)}`;
    if (buscarRol) url += `&rol=${encodeURIComponent(buscarRol)}`;
    if (buscarDep) url += `&dep=${encodeURIComponent(buscarDep)}`;

    const response = await fetch(url);
    const data = await response.json();
    const empleados = Array.isArray(data) ? data : (data.data || []);
    
    renderizarTablaEmpleados(empleados);
  } catch (error) {
    console.error('Error al listar empleados:', error);
    mostrarAlerta('Error al cargar los empleados', 'error');
  }
}

// Renderizar tabla de empleados
function renderizarTablaEmpleados(empleados) {
  const tbody = document.getElementById('tabla-empleados');
  if (!tbody) return;
  
  tbody.innerHTML = '';
  
  if (empleados.length === 0) {
    const tr = document.createElement('tr');
    tr.innerHTML = '<td colspan="9" class="text-center">No se encontraron empleados</td>';
    tbody.appendChild(tr);
    return;
  }

  empleados.forEach(emp => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${emp.id_empleado}</td>
      <td>${emp.nombre}</td>
      <td>${emp.apellidos}</td>
      <td>${emp.dni || ''}</td>
      <td>${emp.telefono || ''}</td>
      <td>${emp.email || ''}</td>
      <td>${emp.nombre_rol || emp.id_rol}</td>
      <td>${emp.nombre_departamento || emp.id_departamento}</td>
      <td>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-primary" onclick="editarEmpleado(${emp.id_empleado})">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn btn-danger" onclick="confirmarEliminarEmpleado(${emp.id_empleado})">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

// Crear nuevo empleado
async function crearEmpleado() {
  try {
    const formData = {
      nombre: document.getElementById('nombre').value,
      apellidos: document.getElementById('apellidos').value,
      dni: document.getElementById('dni').value,
      telefono: document.getElementById('telefono').value,
      email: document.getElementById('email').value,
      direccion: document.getElementById('direccion').value,
      fecha_contrat: document.getElementById('fecha_contrat').value,
      id_rol: document.getElementById('id_rol').value,
      id_departamento: document.getElementById('id_departamento').value
    };

    const response = await fetch('../api/empleados.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(formData)
    });

    const data = await response.json();
    
    if (data.success) {
      mostrarAlerta('Empleado creado con éxito', 'success');
      cerrarModal();
      listarEmpleados();
      cargarEstadisticasEmpleados();
    } else {
      mostrarAlerta(data.error || 'Error al crear el empleado', 'error');
    }
  } catch (error) {
    console.error('Error en crearEmpleado:', error);
    mostrarAlerta('Error al crear el empleado', 'error');
  }
}

// Editar empleado
async function editarEmpleado(idEmpleado) {
  try {
    const response = await fetch(`../api/empleados.php?id=${idEmpleado}`);
    const empleado = await response.json();
    
    if (empleado) {
      // Llenar formulario con datos del empleado
      document.getElementById('id_empleado').value = empleado.id_empleado;
      document.getElementById('nombre').value = empleado.nombre;
      document.getElementById('apellidos').value = empleado.apellidos;
      document.getElementById('dni').value = empleado.dni || '';
      document.getElementById('telefono').value = empleado.telefono || '';
      document.getElementById('email').value = empleado.email || '';
      document.getElementById('direccion').value = empleado.direccion || '';
      document.getElementById('fecha_contrat').value = empleado.fecha_contrat || '';
      document.getElementById('id_rol').value = empleado.id_rol;
      document.getElementById('id_departamento').value = empleado.id_departamento;
      
      // Actualizar título del modal y mostrarlo
      document.getElementById('modalEmpleadoTitulo').textContent = 'Editar Empleado';
      const modal = new bootstrap.Modal(document.getElementById('modalEmpleado'));
      modal.show();
    }
  } catch (error) {
    console.error('Error al cargar empleado para editar:', error);
    mostrarAlerta('Error al cargar los datos del empleado', 'error');
  }
}

// Actualizar empleado
async function actualizarEmpleado(idEmpleado) {
  try {
    const formData = {
      nombre: document.getElementById('nombre').value,
      apellidos: document.getElementById('apellidos').value,
      dni: document.getElementById('dni').value,
      telefono: document.getElementById('telefono').value,
      email: document.getElementById('email').value,
      direccion: document.getElementById('direccion').value,
      fecha_contrat: document.getElementById('fecha_contrat').value,
      id_rol: document.getElementById('id_rol').value,
      id_departamento: document.getElementById('id_departamento').value
    };

    const response = await fetch(`../api/empleados.php?id=${idEmpleado}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(formData)
    });

    const data = await response.json();
    
    if (data.success) {
      mostrarAlerta('Empleado actualizado con éxito', 'success');
      cerrarModal();
      listarEmpleados();
      cargarEstadisticasEmpleados();
    } else {
      mostrarAlerta(data.error || 'Error al actualizar el empleado', 'error');
    }
  } catch (error) {
    console.error('Error en actualizarEmpleado:', error);
    mostrarAlerta('Error al actualizar el empleado', 'error');
  }
}

// Confirmar eliminación de empleado
function confirmarEliminarEmpleado(idEmpleado) {
  if (confirm('¿Está seguro de que desea eliminar este empleado?')) {
    eliminarEmpleado(idEmpleado);
  }
}

// Eliminar empleado
async function eliminarEmpleado(idEmpleado) {
  try {
    const response = await fetch(`../api/empleados.php?id=${idEmpleado}`, {
      method: 'DELETE'
    });

    const data = await response.json();
    
    if (data.success) {
      mostrarAlerta('Empleado eliminado con éxito', 'success');
      listarEmpleados();
      cargarEstadisticasEmpleados();
    } else {
      mostrarAlerta(data.error || 'Error al eliminar el empleado', 'error');
    }
  } catch (error) {
    console.error('Error en eliminarEmpleado:', error);
    mostrarAlerta('Error al eliminar el empleado', 'error');
  }
}

// Resetear formulario
function resetearFormulario() {
  document.getElementById('id_empleado').value = '';
  document.getElementById('formEmpleado').reset();
}

// Cerrar modal
function cerrarModal() {
  const modalEl = document.getElementById('modalEmpleado');
  const modal = bootstrap.Modal.getInstance(modalEl);
  if (modal) {
    modal.hide();
  }
}

// Mostrar alertas
function mostrarAlerta(mensaje, tipo) {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${tipo === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
  alertDiv.style.zIndex = '9999';
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
