# PMS Daniya Denia - Sistema de Gestión Hotelera

## Introducción

PMS-Daniya es un proyecto en desarrollo, ambicioso y en constante evolución, cuyo objetivo es digitalizar y transformar la gestión hotelera. Nace de la observación directa de los retos diarios en la administración hotelera y aspira a convertirse en una solución integral, flexible y escalable para hoteles y alojamientos de cualquier tamaño.

## Descripción del Proyecto

El sistema permite gestionar reservas, clientes, empleados, habitaciones, facturas, mantenimiento y reportes de manera eficiente. El objetivo es proporcionar una plataforma integral que facilite la administración del hotel y mejore la experiencia del cliente y del equipo.

## Estructura del Proyecto

El proyecto está organizado en módulos y carpetas para mantener un código limpio y modular. Ejemplo de estructura:

```
PMS-Daniya/
├── api/
│   ├── checkinout.php
│   ├── clientes.php
│   ├── departamentos.php
│   ├── empleados.php
│   ├── facturas.php
│   ├── habitaciones.php
│   ├── mantenimiento.php
│   ├── ocupacion.php
│   ├── reservas.php
│   └── roles.php
├── config/
│   └── config.php
├── core/
│   ├── Database.php
│   └── SuperModel.php
├── index.php
├── login.php
├── logout.php
├── partials/
│   ├── navbar.php
│   └── sidebar.php
└── public/
    ├── checkin_checkout.php
    ├── clientes.php
    ├── css/
    │   └── style.css
    ├── dashboard.php
    ├── empleados.php
    ├── facturas.php
    ├── habitaciones.php
    ├── js/
    │   └── main.js
    ├── mantenimiento.php
    ├── ocupacion.php
    ├── reportes.php
    └── reservas.php
```

## Funcionalidades Principales

### 1. Gestión de Reservas
- **CRUD de Reservas**: Crear, leer, actualizar y eliminar reservas.
- **Check-in/Check-out**: Gestionar el proceso de entrada y salida de los clientes.
- **Estados de Reserva**: Pendiente, Confirmada, Cancelada, CheckIn, y CheckOut.

### 2. Gestión de Clientes
- **CRUD de Clientes**: Crear, leer, actualizar y eliminar clientes.
- **Búsqueda y Filtros**: Buscar clientes por nombre, apellidos, DNI, etc.

### 3. Gestión de Empleados
- **CRUD de Empleados**: Crear, leer, actualizar y eliminar empleados.
- **Roles y Departamentos**: Asignar roles y departamentos a los empleados.

### 4. Gestión de Habitaciones
- **CRUD de Habitaciones**: Crear, leer, actualizar y eliminar habitaciones.
- **Estados de Habitaciones**: Disponible, Ocupada, y Mantenimiento.

### 5. Gestión de Facturas
- **CRUD de Facturas**: Crear, leer, actualizar y eliminar facturas.
- **Métodos de Pago**: Registrar diferentes métodos de pago.

### 6. Gestión de Mantenimiento
- **CRUD de Incidencias**: Crear, leer, actualizar y eliminar incidencias de mantenimiento.
- **Estados de Incidencias**: Pendiente, En proceso, y Resuelto.

### 7. Ocupación del Hotel
- **Visualización de Ocupación**: Mostrar el estado de ocupación de las habitaciones.
- **Informes de Ocupación**: Generar informes sobre la ocupación del hotel.

### 8. Reportes
- **Generación de Reportes**: Crear reportes de facturación, ocupación histórica, reservas canceladas, etc.
- **Reportes Avanzados**: Gráficos interactivos, exportación en CSV/PDF/XLSX, estadísticas en tiempo real y filtros avanzados.

## Novedades Recientes (v1.2.0 - 4 de mayo de 2025)
- Nuevo módulo de reportes avanzados con gráficos y exportación de datos.
- Mejoras en la gestión de incidencias de mantenimiento y estadísticas en tiempo real.
- Automatización de backups y scripts de despliegue.
- Optimización de consultas SQL y modularización del código.
- Refuerzo de seguridad: validación y sanitización de datos, permisos por rol.
- Mejoras visuales y feedback en la interfaz.

## Estado Actual del Proyecto

El proyecto cuenta con todas las funcionalidades principales implementadas y en uso. Se han añadido módulos avanzados de reportes, mejoras de rendimiento, seguridad y automatización. El sistema está en constante evolución y optimización.

## Futuras Mejoras
1. **Sistema de notificaciones en tiempo real** para empleados y eventos importantes.
2. **Integración con plataformas externas** de reservas y sistemas de pago.
3. **Dashboard personalizable** y reportes a medida.
4. **Mejoras continuas en la interfaz de usuario** y experiencia de usuario.
5. **Análisis de datos avanzado** para la toma de decisiones.

## Automatización de copias de seguridad con cron

Para asegurar la integridad de los datos, se ha configurado una tarea cron en macOS que ejecuta el script `backup.sh` cada 3 días a las 2:00 AM. Este script realiza una copia de seguridad de la base de datos MySQL y la almacena en la carpeta `backups`.

### Configuración de cron en macOS

1. Abre la terminal.
2. Ejecuta:
   ```bash
   export VISUAL=nano; crontab -e
   ```
3. Añade la siguiente línea al final del archivo:
   ```cron
   0 2 */3 * * /bin/bash /ruta/a/PMS-Daniya/backup.sh
   ```
4. Guarda y cierra el editor.

El sistema ejecutará automáticamente la copia de seguridad cada 3 días.

### ¿Qué hace el script backup.sh?
- Lee las credenciales de la base de datos desde el archivo `.env`.
- Comprueba si han pasado al menos 3 días desde el último backup.
- Si corresponde, genera un archivo `.sql` con el volcado de la base de datos en la carpeta `backups`.
- Muestra mensajes de éxito o error según el resultado.

Puedes ejecutar el script manualmente con:

```bash
./backup.sh
```

## Seguridad y gestión de credenciales

Para proteger la información sensible, las credenciales de la base de datos y otros datos privados se almacenan en el archivo `.env`, que está incluido en `.gitignore` y nunca se sube al repositorio de GitHub. De igual forma, el archivo `config/config.php` también está excluido del control de versiones.

**Importante:**
- Nunca subas archivos con contraseñas o datos sensibles al repositorio.
- Comparte el archivo `.env` solo de forma segura y privada con los miembros autorizados del equipo.

Antes de subir el proyecto a GitHub, revisa siempre que `.env` y otros archivos sensibles estén correctamente excluidos.

## Conclusión

PMS-Daniya es una solución integral para la gestión hotelera, en constante evolución y con una visión de futuro. Con las mejoras planificadas, el sistema se convertirá en una herramienta aún más poderosa y eficiente para la administración del hotel.

---

Este documento proporciona una visión general del proyecto, su estructura, funcionalidades, estado actual y futuras mejoras. Para más detalles técnicos o específicos, consulta el código fuente y la documentación adicional.
