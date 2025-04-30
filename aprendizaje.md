# Aprendizaje sobre el Proyecto PMS-Daniya

## Programación

### 1. Elementos fundamentales
Nuestro código utiliza variables (por ejemplo, `$usuario`, `$password`), constantes (`define('DB_HOST', ...)`), operadores aritméticos (`+`, `-`), lógicos (`&&`, `||`) y de comparación (`==`, `!=`). Los tipos de datos principales son cadenas (string), números (int, float), booleanos y arrays.

```php
$nombre = "Juan";
$edad = 25;
$esAdmin = true;
$usuarios = ["Juan", "Ana", "Luis"];
```

### 2. Estructuras de control
Usamos estructuras de selección (`if`, `else`, `switch`) para tomar decisiones y bucles (`for`, `foreach`, `while`) para repetir acciones. Por ejemplo, para mostrar todos los empleados:

```php
foreach ($empleados as $empleado) {
    echo $empleado["nombre"];
}
```

### 3. Control de excepciones y gestión de errores
En PHP, usamos `try-catch` para capturar excepciones, especialmente al conectar con la base de datos o enviar emails. También comprobamos errores con condicionales y mostramos mensajes personalizados.

```php
try {
    $db = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
```

### 4. Documentación del código
Comentamos el código con `//` y `/* ... */` para explicar partes importantes. En clases y funciones, usamos docstrings para describir su propósito y parámetros.

### 5. Paradigma aplicado
El proyecto combina programación estructurada y orientada a objetos (POO). Usamos POO para organizar el código en clases como `Database`, `EmailService` y modelos de datos, facilitando el mantenimiento y la reutilización.

### 6. Clases y objetos principales
- `Database`: gestiona la conexión y consultas a la base de datos.
- `EmailService`: envía correos electrónicos.
- `SuperModel`: clase base para modelos de datos.
Estas clases se relacionan usando composición y herencia.

### 7. Conceptos avanzados
Utilizamos herencia (por ejemplo, modelos que extienden `SuperModel`) y polimorfismo para reutilizar y adaptar funcionalidades. No usamos interfaces explícitas, pero sí métodos abstractos en clases base.

### 8. Gestión de información y archivos
Leemos y escribimos archivos (por ejemplo, logs o exportaciones). La interacción principal con el usuario es vía web (formularios HTML y respuestas PHP).

### 9. Estructuras de datos
Usamos arrays para listas de datos (empleados, habitaciones, reservas) y matrices asociativas para representar registros.

### 10. Técnicas avanzadas
Aplicamos expresiones regulares para validar emails y otros datos. Usamos flujos de entrada/salida para leer y escribir archivos.

---

## Sistemas Informáticos

### 1. Hardware
Desarrollamos en ordenadores personales (PC/Mac) con procesadores Intel/Apple Silicon, 8GB+ de RAM. El entorno de producción es un servidor web con características similares.

### 2. Sistema operativo
Usamos macOS para desarrollo y Linux (por ejemplo, Ubuntu Server) para producción, por su estabilidad y soporte para PHP/MySQL.

### 3. Redes
El proyecto funciona en una red local (LAN) y puede accederse desde Internet. Usamos HTTP/HTTPS y configuramos el firewall para limitar accesos.

### 4. Copias de seguridad
Realizamos copias de seguridad periódicas de la base de datos y archivos importantes, usando scripts automáticos y almacenamiento externo.

### 5. Seguridad e integridad
Protegemos los datos con contraseñas seguras, cifrado en la base de datos y validación de entradas. Limitamos permisos de archivos y usuarios.

### 6. Usuarios y permisos
Configuramos usuarios en el sistema operativo y en la base de datos con permisos mínimos necesarios.

### 7. Documentación técnica
Mantenemos documentación en archivos markdown y README para la configuración y gestión del sistema.

---

## Entornos de Desarrollo

### 1. IDE
Utilizamos Visual Studio Code, configurado con extensiones para PHP, HTML, CSS y Git.

### 2. Automatización de tareas
Automatizamos tareas como la instalación de dependencias con Composer y scripts para copias de seguridad.

### 3. Control de versiones
Usamos Git y GitHub para gestionar el código, versiones y ramas. Creamos ramas para nuevas funcionalidades y corregimos errores en ramas separadas.

### 4. Refactorización
Revisamos y mejoramos el código periódicamente para hacerlo más eficiente y legible.

### 5. Documentación técnica
Documentamos el proyecto con archivos markdown (`README.md`, `RELEASE.md`) y comentarios en el código.

### 6. Diagramas
Creamos diagramas de clases y de flujo para planificar la estructura y el comportamiento de la aplicación.

---

## Bases de Datos

### 1. SGBD
Usamos MySQL por su integración con PHP y facilidad de uso.

### 2. Modelo entidad-relación
Diseñamos un modelo con tablas para empleados, clientes, habitaciones, reservas, etc., y relaciones entre ellas (uno a muchos, muchos a muchos).

### 3. Funcionalidades avanzadas
Utilizamos vistas para consultas complejas y procedimientos almacenados para operaciones repetitivas.

### 4. Protección y recuperación de datos
Implementamos copias de seguridad y validaciones para evitar pérdidas o corrupciones.

---

## Lenguajes de Marcas y Gestión de Información

### 1. Estructura HTML
Estructuramos los documentos HTML con etiquetas semánticas (`<header>`, `<nav>`, `<main>`, `<footer>`) y seguimos buenas prácticas.

### 2. Tecnologías frontend
Usamos CSS para el diseño y JavaScript para la interactividad. Elegimos estas tecnologías por su compatibilidad y facilidad de uso.

### 3. Interacción con el DOM
Utilizamos JavaScript para modificar el DOM dinámicamente, por ejemplo, mostrando mensajes o actualizando tablas.

### 4. Validación
Validamos HTML y CSS con herramientas online para asegurar la compatibilidad.

### 5. Conversión de datos
Convertimos datos entre formatos (por ejemplo, JSON para respuestas de la API) para facilitar la comunicación entre frontend y backend.

### 6. Aplicación de gestión empresarial
Nuestra aplicación es un software de gestión hotelera, permitiendo controlar reservas, clientes, empleados, facturación, etc.

---

## Proyecto Intermodular

### 1. Objetivo
El software gestiona un hotel, facilitando la administración de reservas, clientes, habitaciones y empleados.

### 2. Necesidad o problema
Resuelve la gestión manual y dispersa de la información en hoteles, centralizando y automatizando procesos.

### 3. Stack tecnológico
PHP, MySQL, HTML, CSS, JavaScript. Elegimos este stack por su robustez, facilidad de aprendizaje y amplia documentación.

### 4. Desarrollo por versiones
Comenzamos con una versión mínima funcional (gestión básica de reservas y clientes) y añadimos nuevas funcionalidades en versiones posteriores (facturación, reportes, marketing, etc).

---

Este documento resume los aspectos clave del desarrollo y la gestión del proyecto PMS-Daniya, pensado para estudiantes de DAM y fácil de entender.