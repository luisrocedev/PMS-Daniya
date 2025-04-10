# PMS Daniya Denia - Sistema de Gestión Hotelera

## Descripción del Proyecto

El proyecto PMS (Property Management System) Daniya Denia es un sistema de gestión hotelera diseñado para optimizar las operaciones diarias de un hotel. Este sistema permite gestionar reservas, clientes, empleados, habitaciones, facturas, y mantenimiento de manera eficiente. El objetivo es proporcionar una plataforma integral que facilite la administración del hotel y mejore la experiencia del cliente.

## Estructura del Proyecto

El proyecto está organizado en varios módulos y carpetas para mantener un código limpio y modular. A continuación, se describe la estructura del proyecto:

```
C:\xampp\htdocs\PMS
├── api
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
├── config
│   └── config.php
├── core
│   ├── Database.php
│   └── SuperModel.php
├── index.php
├── login.php
├── logout.php
├── partials
│   ├── navbar.php
│   └── sidebar.php
└── public
    ├── checkin_checkout.php
    ├── clientes.php
    ├── css
    │   └── style.css
    ├── dashboard.php
    ├── empleados.php
    ├── facturas.php
    ├── habitaciones.php
    ├── js
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
- **Estados de Reserva**: Manejar diferentes estados como Pendiente, Confirmada, Cancelada, CheckIn, y CheckOut.

### 2. Gestión de Clientes
- **CRUD de Clientes**: Crear, leer, actualizar y eliminar clientes.
- **Búsqueda y Filtros**: Buscar clientes por nombre, apellidos, DNI, etc.

### 3. Gestión de Empleados
- **CRUD de Empleados**: Crear, leer, actualizar y eliminar empleados.
- **Roles y Departamentos**: Asignar roles y departamentos a los empleados.

### 4. Gestión de Habitaciones
- **CRUD de Habitaciones**: Crear, leer, actualizar y eliminar habitaciones.
- **Estados de Habitaciones**: Gestionar estados como Disponible, Ocupada, y Mantenimiento.

### 5. Gestión de Facturas
- **CRUD de Facturas**: Crear, leer, actualizar y eliminar facturas.
- **Métodos de Pago**: Registrar diferentes métodos de pago.

### 6. Gestión de Mantenimiento
- **CRUD de Incidencias**: Crear, leer, actualizar y eliminar incidencias de mantenimiento.
- **Estados de Incidencias**: Gestionar estados como Pendiente, En proceso, y Resuelto.

### 7. Ocupación del Hotel
- **Visualización de Ocupación**: Mostrar el estado de ocupación de las habitaciones.
- **Informes de Ocupación**: Generar informes sobre la ocupación del hotel.

### 8. Reportes
- **Generación de Reportes**: Crear reportes de facturación, ocupación histórica, reservas canceladas, etc.

## Estado Actual del Proyecto

El proyecto está actualmente en desarrollo. Las funcionalidades básicas están implementadas, pero hay varias áreas que requieren mejoras y optimizaciones.

## Futuras Mejoras

1. **Mejora de la Interfaz de Usuario**: Rediseñar la interfaz para hacerla más intuitiva y fácil de usar.
2. **Optimización del Rendimiento**: Mejorar el rendimiento de las consultas a la base de datos y la carga de datos en la interfaz.
3. **Seguridad**: Implementar medidas adicionales de seguridad para proteger los datos sensibles.
4. **Notificaciones**: Añadir un sistema de notificaciones para alertar a los empleados sobre eventos importantes.
5. **Integración con Sistemas Externos**: Integrar el sistema con otros servicios como sistemas de pago en línea, plataformas de reservas, etc.
6. **Análisis de Datos**: Implementar herramientas de análisis de datos para proporcionar información valiosa sobre el rendimiento del hotel.

## Conclusión

El PMS Daniya Denia es una solución integral para la gestión hotelera que está en constante evolución. Con las mejoras planificadas, el sistema se convertirá en una herramienta aún más poderosa y eficiente para la administración del hotel.

---

Este documento proporciona una visión general del proyecto, su estructura, funcionalidades, estado actual y futuras mejoras. Para más detalles técnicos o específicos, consulte el código fuente y la documentación adicional.
