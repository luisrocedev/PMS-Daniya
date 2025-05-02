---
marp: true
theme: gaia
paginate: true
---

# 🗂️ Aprendizaje sobre el Proyecto PMS-Daniya

---

# Programación

## 1. Elementos fundamentales del código
- Uso de variables y constantes en PHP.
- Tipos: string, int, float, boolean, array, objeto.
- Ejemplo:
```php
const DB_HOST = 'localhost';
$clientes = array();
```

---

## 2. Estructuras de control
- Condicionales: if, else, switch.
- Bucles: for, foreach, while.
- Ejemplo:
```php
foreach ($clientes as $cliente) {
  // ...
}
```

---

## 3. Control de excepciones y gestión de errores
- Uso de try-catch en PHP para manejar errores de base de datos y lógica.

---

## 4. Documentación del código
- Comentarios en PHP y archivos markdown (README, aprendizaje, guion).

---

## 5. Paradigma aplicado
- Programación orientada a objetos y modular.
- Separación de lógica en controladores, modelos y vistas.

---

## 6. Clases y objetos principales
- Clases: Cliente, Reserva, Habitacion, Factura.
- Uso de objetos y arrays para gestionar datos.

---

## 7. Conceptos avanzados
- Conexión a MySQL con PDO o MySQLi.
- Generación de informes y backups automáticos.
- Modularidad y reutilización de funciones.

---

## 8. Gestión de información y archivos
- Uso de base de datos MySQL.
- Exportación/importación de datos en SQL y JSON.

---

## 9. Estructuras de datos utilizadas
- Arrays y objetos para clientes, reservas, habitaciones y facturas.

---

## 10. Técnicas avanzadas
- Scripts de backup en bash y PHP.
- Validación de formularios y gestión de sesiones.

---

# Sistemas Informáticos

## 1. Características del hardware
- Desarrollo y pruebas en MacBook (macOS), compatible con cualquier servidor PHP.

---

## 2. Sistema operativo
- Multiplataforma: macOS, Linux, Windows (con XAMPP/MAMP/WAMP).

---

## 3. Configuración de redes
- Acceso por HTTP en red local o internet.

---

## 4. Copias de seguridad
- Scripts automáticos de backup y uso de Git para control de versiones.

---

## 5. Integridad y seguridad de datos
- Validación de entradas y gestión de sesiones.
- Uso de permisos y autenticación básica.

---

## 6. Usuarios, permisos y accesos
- Gestión de usuarios y roles en la aplicación.

---

## 7. Documentación técnica
- Archivos markdown y comentarios en el código.

---

# Entornos de Desarrollo

## 1. Entorno de desarrollo (IDE)
- Visual Studio Code con extensiones para PHP y SQL.

---

## 2. Automatización de tareas
- Scripts de backup y despliegue.

---

## 3. Control de versiones
- Git y GitHub.

---

## 4. Refactorización
- Mejoras periódicas en la estructura y modularidad del código.

---

## 5. Documentación técnica
- README.md, aprendizaje.md, guion.md.

---

## 6. Diagramas
- Opcional: diagramas de flujo para la arquitectura del sistema.

---

# Bases de Datos

## 1. Sistema gestor
- MySQL para almacenamiento de datos.

---

## 2. Modelo entidad-relación
- Tablas: clientes, reservas, habitaciones, facturas, usuarios.

---

## 3. Funcionalidades avanzadas
- Consultas complejas y generación de informes.

---

## 4. Protección y recuperación de datos
- Backups automáticos y restauración desde SQL.

---

# Lenguajes de Marcas y Gestión de Información

## 1. Estructura de HTML
- Uso de etiquetas semánticas en las vistas.

---

## 2. Tecnologías frontend
- HTML, CSS, JavaScript.

---

## 3. Interacción con el DOM
- JS para validación y mejora de formularios.

---

## 4. Validación de HTML y CSS
- Validadores online y extensiones del IDE.

---

## 5. Conversión de datos (XML, JSON)
- Exportación/importación de datos en JSON y SQL.

---

## 6. Integración con sistemas de gestión
- Posibilidad de integración con otros sistemas mediante exportaciones.

---

# Proyecto Intermodular

## 1. Objetivo del software
- Facilitar la gestión integral de un hotel.

---

## 2. Necesidad o problema que soluciona
- Centraliza reservas, clientes, habitaciones y facturación.

---

## 3. Stack de tecnologías
- PHP, MySQL, HTML, CSS, JavaScript, bash.

---

## 4. Desarrollo por módulos
- Módulo de reservas, clientes, habitaciones, facturación y utilidades.

---

<style>
section code, section pre {
  font-size: 0.8em;
}
.small-code code, .small-code pre {
  font-size: 0.7em;
}
</style>