# Guía para Publicaciones de LinkedIn – Proyecto "PMS-Daniya"

Esta guía te ayudará a preparar y realizar publicaciones de LinkedIn sobre el proyecto PMS-Daniya, adaptadas a cada asignatura. Puedes copiar y completar los ejemplos durante el examen.

---

## Lenguajes de Marcas

🏨 **Presentando “PMS-Daniya” – Lenguajes de Marcas**

La interfaz de PMS-Daniya está desarrollada con HTML5 y CSS3, permitiendo una experiencia de usuario clara y profesional para la gestión hotelera.

Ejemplo de código:

**<**form\*\* **id**=**"login-form"**>\*\*

** <**input\*\* **type**=**"text"** **name**=**"usuario"** **placeholder**=**"Usuario"** />\*\*

** <**input\*\* **type**=**"password"** **name**=**"password"** **placeholder**=**"Contraseña"** />\*\*

** <**button\*\* **type**=**"submit"**>Entrar</**button**>\*\*

**</**form**>**

[Sube aquí una captura de la pantalla de login o dashboard]

---

## Sistemas Informáticos

🔒 **Seguridad y rendimiento en “PMS-Daniya” – Sistemas Informáticos**

El backend utiliza PHP y buenas prácticas de seguridad, como la gestión de sesiones y la validación de entradas.

Ejemplo de código:

**<?php**

**session_start**(**)**;

**if** **(**isset**(**$_POST**[**'usuario'**]**)** **&&** **isset**(**$\_POST**[**'password'**]**)**)** **{**

\*\* \*\*// Validación y autenticación

**}**

[Incluye aquí un diagrama de arquitectura o consola mostrando logs]

---

## Base de Datos

📊 **Gestión de datos en “PMS-Daniya” – Base de Datos**

PMS-Daniya gestiona reservas, clientes y empleados usando una base de datos SQL, permitiendo consultas y operaciones eficientes.

Ejemplo de código:

**<?php**

**// Conexión y consulta**

**$conn** **=** **new** **mysqli**(**$host**, **$user**, **$pass**, **$db**)**;**

**$result** **=** **$conn**->**query**(**"**SELECT** \*\*\*** **FROM** reservas**"**)\*\*;

[Adjunta aquí un fragmento de la base de datos o una consulta ejemplo]

---

## Entornos de Desarrollo

⚙️ **Desarrollo ágil y despliegue en “PMS-Daniya” – Entornos de Desarrollo**

El proyecto utiliza scripts y herramientas para facilitar el desarrollo, backup y despliegue.

Ejemplo de script:

**# backup.sh**

**mysqldump** **-u** **usuario** **-p** **base_de_datos** > **backup.sql**

[Incluye una captura de la terminal ejecutando un script de backup o despliegue]

---

## Programación

💻 **Lógica y algoritmia en “PMS-Daniya” – Programación**

La lógica de negocio se desarrolla en PHP y JavaScript, gestionando operaciones como reservas, check-in/out y control de usuarios.

Ejemplo de código:

**<?php**

**function** **registrarReserva**(**$datos**)\*\* \*\*{

\*\* **// Lógica para registrar una reserva en la base de **datos\*\*

**}**

[Incluye aquí un diagrama de flujo o fragmento de la lógica de reservas]

---

## Proyecto Intermodular

🤝 **Integración total: “PMS-Daniya” – Proyecto Intermodular**

PMS-Daniya es el resultado de la integración de conocimientos de todas las asignaturas, desde la interfaz hasta la gestión de datos y lógica de negocio.

Ejemplo de función:

**<?php**

**function** **checkin**(**$usuario**, **$fecha**)\*\* \*\*{

\*\* **// Lógica para registrar el check-in de un **empleado\*\*

**}**

[Sube un gif o imagen del sistema funcionando en tiempo real]
