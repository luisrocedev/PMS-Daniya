# Guion Hablado para la Explicación del Proyecto PMS-Daniya

---

## Introducción

Hola, soy [tu nombre] y en este vídeo voy a presentar el proyecto PMS-Daniya, un software de gestión hotelera desarrollado principalmente en PHP, MySQL, JavaScript, HTML y CSS. A lo largo de la presentación, mostraré cómo se han abordado los resultados de aprendizaje de los diferentes módulos, enseñando ejemplos concretos en el código y la aplicación.

---

## 1. Programación

### a) Elementos Fundamentales

En [Database.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) defino variables y constantes para la conexión a la base de datos:

**<?php**

**private** **$host** **=** **"localhost"**;

**private** **$db_name** **=** **"pms_daniya"**;

**private** **$username** **=** **"root"**;

**private** **$password** **=** **""**;

Aquí se usan tipos de datos como strings y booleanos para la configuración y control de la conexión.

---

### b) Estructuras de Control

En [clientes.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) uso condicionales y bucles:

**<?php**

**if** **(**$_SERVER**[**'REQUEST_METHOD'**]** **===** **'POST'**)** **{

**    **// Procesar datos del cliente

**    **foreach** **(**$clientes** **as** **$cliente**)** **{

**        **if** **(**$cliente**[**'activo'**]**)** **{**

**            **// ...procesar cliente activo...

**        **}

**    **}

**}**

Esto permite controlar el flujo según el tipo de petición y recorrer listas de clientes.

---

### c) Control de Excepciones y Errores

En [Database.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) gestiono errores de conexión con try/catch:

**<?php**

**try** **{**

**    **$this**->**conn** **=** **new** **PDO**(**$dsn**, **$this**->**username**, **$this**->**password**)**;

**}** **catch** **(**PDOException** **$exception**)** **{**

**    **echo** **"Error de conexión: "** **.** **$exception**->**getMessage**(**)**;**

**}**

Así, cualquier error de conexión es capturado y se muestra un mensaje controlado.

---

### d) Documentación del Código

En [EmailService.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) documento las funciones:

**<?php**

**/****

** * Envía un email de notificación al cliente.**

** * **@param** **string** $to**

** * **@param** **string** $subject**

** * **@param** **string** $message**

** */**

**public** **function** **sendEmail**(**$to**, **$subject**, **$message**)** **{

**    **// ...código...

**}**

Esto facilita la comprensión y el mantenimiento del código.

---

### e) Paradigma de Programación

El proyecto sigue el paradigma orientado a objetos.
Por ejemplo, en [SuperModel.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html):

**<?php**

**class** **SuperModel** **{**

**    **protected** **$db**;**

**    **public** **function** **__construct**(**$db**)** **{**

**        **$this**->**db** **=** **$db**;**

**    **}

**    **// Métodos comunes para los modelos

**}**

Esto permite reutilizar y organizar la lógica de acceso a datos.

---

### f) Clases y Objetos Principales

En [Database.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html), [EmailService.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) y [SuperModel.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) se definen las clases principales.
Por ejemplo, en [Database.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html):

**<?php**

**$db** **=** **new** **Database**(**)**;

**$conn** **=** **$db**->**getConnection**(**)**;

Así gestiono la conexión a la base de datos de forma centralizada.

---

### g) Conceptos Avanzados

En [SuperModel.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) uso herencia para que otros modelos extiendan funcionalidades comunes:

**<?php**

**class** **ClienteModel** **extends** **SuperModel** **{**

**    **// Métodos específicos para clientes

**}**

Esto permite aplicar polimorfismo y reutilización de código.

---

### h) Gestión de Información

La información se almacena en la base de datos MySQL, pero también se gestionan archivos para copias de seguridad, como en [backup.sh](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) y [ejecutar_backup.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html):

**mysqldump** **-u** **root** **pms_daniya** > **backup.sql**

Y en PHP:

**<?php**

**exec**(**"sh backup.sh"**)**;**

---

### i) Estructuras de Datos

En [reservas.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) uso arrays para manejar reservas:

**<?php**

**$reservas** **=** **[**]**;**

**while** **(**$row** **=** **$stmt**->**fetch**(**PDO**::**FETCH_ASSOC**)**)** **{

**    **$reservas**[**]** **=** **$row**;**

**}**

Esto facilita la manipulación y el envío de datos en formato JSON.

---

### j) Técnicas Avanzadas

En [EmailService.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) uso expresiones regulares para validar emails:

**<?php**

**if** **(**!preg_match**(**"/**^**[^@]**+**@[^@]**+**\.**[a-zA-Z]{2,}**$**/"**,** **$email**)**)** **{

**    **// Email no válido

**}**

También uso flujos de entrada/salida para la gestión de archivos y backups.

---

## 2. Sistemas Informáticos

### a) Hardware y Entornos

Desarrollo en Windows con XAMPP, pero el sistema puede desplegarse en cualquier servidor compatible con PHP y MySQL.

---

### b) Sistema Operativo

He elegido Windows para el desarrollo por comodidad y compatibilidad con XAMPP, aunque el despliegue puede hacerse en Linux.

---

### c) Configuración de Redes

El sistema funciona en red local o en la nube, accediendo a través de HTTP.
Por ejemplo, accedo desde el navegador a `localhost/PMS-Daniya/public/dashboard.php`.

---

### d) Copias de Seguridad

Realizo copias de seguridad con el script [backup.sh](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) y el archivo [ejecutar_backup.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html), que ejecuta el backup desde la web.

---

### e) Seguridad e Integridad de Datos

En [roles.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) y [empleados.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) gestiono el control de acceso por roles:

**<?php**

**if** **(**$usuario**[**'rol'**]** **!==** **'admin'**)** **{

**    **http_response_code**(**403**)**;

**    **exit**(**'Acceso denegado'**)**;

**}**

---

### f) Gestión de Usuarios y Permisos

En [empleados.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) gestiono la creación y modificación de empleados, asignando roles y permisos desde la base de datos.

---

### g) Documentación Técnica

Toda la documentación técnica está en [README.md](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) y [RELEASE.md](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html), donde explico la instalación, configuración y uso del sistema.

---

## 3. Entornos de Desarrollo

### a) IDE y Configuración

Utilizo Visual Studio Code con extensiones para PHP, SQL y control de versiones.

---

### b) Automatización de Tareas

Uso Composer para la gestión de dependencias, definido en [composer.json](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html):

**{**

**  **"require"**: **{

**    **"phpmailer/phpmailer"**: **"^6.0"

**  **}

**}**

---

### c) Control de Versiones

Uso Git y GitHub para gestionar el código y las ramas del proyecto.

---

### d) Refactorización

Refactorizo el código centralizando la lógica común en [SuperModel.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) y separando la lógica de negocio en diferentes archivos y carpetas.

---

### e) Documentación Técnica

Toda la documentación técnica está en el archivo [README.md](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html).

---

### f) Diagramas

Incluyo diagramas de base de datos y arquitectura en la documentación para explicar la estructura del sistema.

---

## 4. Bases de Datos

### a) Sistema Gestor

Uso MySQL como sistema gestor de bases de datos, configurado en [Database.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) y definido en [basededatos.sql](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html).

---

### b) Modelo Entidad-Relación

En [basededatos.sql](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) defino las tablas y relaciones de la base de datos, reflejando la relación entre clientes, reservas, habitaciones, empleados, etc.

---

### c) Funcionalidades Avanzadas

Puedo añadir triggers o procedimientos almacenados en la base de datos para automatizar tareas, aunque la lógica principal reside en el backend.

---

### d) Protección y Recuperación de Datos

Realizo backups periódicos de la base de datos y guardo logs en archivos para auditoría y recuperación.

---

## 5. Lenguajes de Marcas y Gestión de Información

### a) Estructura HTML y Buenas Prácticas

En [clientes.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) uso etiquetas semánticas:

**<**main**>**

**  <**section** **id**=**"clientes"**></**section**>**

**</**main**>**

Esto mejora la accesibilidad y la estructura del contenido.

---

### b) Tecnologías Frontend

Utilizo CSS para el diseño (en [css](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html)) y JavaScript para la lógica de la interfaz (en [js](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html)).

---

### c) Interacción con el DOM

En [main.js](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) manipulo el DOM para actualizar la interfaz:

**document**.**getElementById**(**'btnAddCliente'**)**.**addEventListener**(**'click'**, **(**)** **=>** **{**

**  **// Actualizar interfaz

**}**)**;**

---

### d) Validación

Valido los formularios tanto en el frontend como en el backend para asegurar la integridad de los datos.

---

### e) Conversión de Datos

En [clientes.php](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) envío y recibo datos en formato JSON:

**<?php**

**echo** **json_encode**(**$clientes**)**;**

---

### f) Integración con Sistemas de Gestión Empresarial

PMS-Daniya es una aplicación de gestión empresarial para hoteles, centralizando reservas, clientes, empleados y facturación.

---

## 6. Proyecto Intermodular

### a) Objetivo del Software

El objetivo es facilitar la gestión integral de un hotel, permitiendo controlar reservas, clientes, empleados y facturación desde una única plataforma.

---

### b) Stack Tecnológico

Uso PHP, MySQL, JavaScript, HTML, CSS y Composer por su robustez y facilidad de integración.

---

### c) Desarrollo por Versiones

Empecé con una versión mínima funcional y fui añadiendo módulos como facturación, reportes y marketing.
Puedo mostrar el historial de commits en GitHub para ilustrar este proceso.

---

## 7. Evaluación y Entrega

Entrego vídeos demostrando el funcionamiento, el código fuente en GitHub y la documentación en [README.md](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html) y [RELEASE.md](vscode-file://vscode-app/c:/Users/Luis/AppData/Local/Programs/Microsoft%20VS%20Code/resources/app/out/vs/code/electron-sandbox/workbench/workbench.html).

---

## Despedida

Esto ha sido un recorrido completo por el proyecto PMS-Daniya, mostrando cómo se han abordado todos los resultados de aprendizaje requeridos.
Gracias por vuestra atención.
