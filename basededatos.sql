CREATE DATABASE IF NOT EXISTS pms_daniya_denia;
USE pms_daniya_denia;

CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL
);

INSERT INTO roles (nombre_rol)
VALUES 
    ('Recepcionista'),
    ('Camarero'),
    ('Cocinero'),
    ('Jefe de Mantenimiento'),
    ('Mantenimiento'),
    ('Gobernanta'),
    ('Limpieza'),
    ('Gerente');

CREATE TABLE departamentos (
    id_departamento INT AUTO_INCREMENT PRIMARY KEY,
    nombre_departamento VARCHAR(100) NOT NULL
);

INSERT INTO departamentos (nombre_departamento)
VALUES
    ('Recepción'),
    ('Restaurante'),
    ('Mantenimiento'),
    ('Pisos'),
    ('Administración');

CREATE TABLE empleados (
    id_empleado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(50) NOT NULL,
    telefono VARCHAR(50),
    email VARCHAR(100),
    direccion VARCHAR(200),
    fecha_contratacion DATE,
    id_rol INT NOT NULL,
    id_departamento INT NOT NULL,
    CONSTRAINT fk_empleado_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
    CONSTRAINT fk_empleado_departamento FOREIGN KEY (id_departamento) REFERENCES departamentos(id_departamento)
);

INSERT INTO empleados (nombre, apellidos, dni, telefono, email, direccion, fecha_contratacion, id_rol, id_departamento)
VALUES
    ('Ana', 'García López', '12345678A', '600111222', 'ana.garcia@daniyadenia.com', 'C/ Mar, 1, Denia', '2021-04-10', 1, 1), 
    ('Carlos', 'Pérez Muñoz', '87654321B', '600333444', 'carlos.perez@daniyadenia.com', 'Av. Mediterráneo, 15, Denia', '2020-09-15', 2, 2),
    ('María', 'Sánchez Ruiz', '11122233C', '600555666', 'maria.sanchez@daniyadenia.com', 'C/ Estrella, 10, Denia', '2019-05-20', 3, 2),
    ('Javier', 'López Romero', '44455566D', '600777888', 'javier.lopez@daniyadenia.com', 'C/ Puerto, 8, Denia', '2018-02-01', 4, 3),
    ('Lucía', 'Martín Torres', '99988877E', '600999000', 'lucia.martin@daniyadenia.com', 'C/ Dársena, 22, Denia', '2021-07-01', 5, 3),
    ('Pilar', 'Hernández Vives', '22233344F', '600444555', 'pilar.hernandez@daniyadenia.com', 'C/ Fénix, 4, Denia', '2017-11-10', 6, 4),
    ('Andrea', 'Martínez Rey', '55566677G', '600222111', 'andrea.martinez@daniyadenia.com', 'Av. Alicante, 33, Denia', '2022-01-15', 7, 4),
    ('Ricardo', 'Giménez Sáez', '77788899H', '600000123', 'ricardo.gimenez@daniyadenia.com', 'Plaza Mayor, 2, Denia', '2016-03-10', 8, 5);

CREATE TABLE habitaciones (
    id_habitacion INT AUTO_INCREMENT PRIMARY KEY,
    numero_habitacion VARCHAR(10) NOT NULL,
    tipo_habitacion VARCHAR(50) NOT NULL,       -- Ejemplo: "Doble", "Individual", "Suite"
    capacidad INT NOT NULL,                     -- Ejemplo: 2, 4...
    piso INT NOT NULL,
    estado VARCHAR(50) NOT NULL                 -- Ejemplo: "Disponible", "Ocupada", "Mantenimiento"
);

INSERT INTO habitaciones (numero_habitacion, tipo_habitacion, capacidad, piso, estado)
VALUES
    ('101', 'Doble', 2, 1, 'Disponible'),
    ('102', 'Individual', 1, 1, 'Disponible'),
    ('103', 'Doble', 2, 1, 'Mantenimiento'),
    ('201', 'Suite', 4, 2, 'Disponible'),
    ('202', 'Doble', 2, 2, 'Ocupada'),
    ('203', 'Doble Superior', 2, 2, 'Disponible');

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(50),
    direccion VARCHAR(200)
);

INSERT INTO clientes (nombre, apellidos, dni, email, telefono, direccion)
VALUES
    ('Sergio', 'López Alba', '33445566J', 'sergio.lopez@example.com', '600111333', 'C/ Palma, 5, Madrid'),
    ('Beatriz', 'Rodríguez Sanz', '12312312K', 'beatriz.rodriguez@example.com', '600444777', 'C/ Colón, 9, Valencia'),
    ('Juan', 'Martínez Díaz', '99977755L', 'juan.martinez@example.com', '700888999', 'Av. Andalucía, 25, Sevilla');

CREATE TABLE reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_habitacion INT NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado_reserva VARCHAR(50) NOT NULL,  -- Ej. "Pendiente", "Confirmada", "Cancelada"
    CONSTRAINT fk_reserva_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    CONSTRAINT fk_reserva_habitacion FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id_habitacion)
);

INSERT INTO reservas (id_cliente, id_habitacion, fecha_entrada, fecha_salida, estado_reserva)
VALUES
    (1, 1, '2025-04-15', '2025-04-17', 'Confirmada'),
    (2, 5, '2025-05-01', '2025-05-05', 'Pendiente'),
    (3, 6, '2025-06-10', '2025-06-12', 'Confirmada');

CREATE TABLE servicios_restaurante (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    fecha DATE NOT NULL,
    descripcion VARCHAR(200) NOT NULL,
    costo DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_servrest_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva)
);

INSERT INTO servicios_restaurante (id_reserva, fecha, descripcion, costo)
VALUES
    (1, '2025-04-16', 'Cena en el restaurante principal', 50.00),
    (3, '2025-06-11', 'Almuerzo buffet', 30.00);

CREATE TABLE mantenimiento (
    id_incidencia INT AUTO_INCREMENT PRIMARY KEY,
    id_habitacion INT NOT NULL,
    id_empleado INT NOT NULL,  -- Empleado de mantenimiento asignado
    descripcion VARCHAR(200) NOT NULL,
    fecha_reporte DATE NOT NULL,
    fecha_resolucion DATE,
    estado VARCHAR(50) NOT NULL,  -- Ej. "Pendiente", "En proceso", "Resuelto"
    CONSTRAINT fk_mantenimiento_habitacion FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id_habitacion),
    CONSTRAINT fk_mantenimiento_empleado FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
);

INSERT INTO mantenimiento (id_habitacion, id_empleado, descripcion, fecha_reporte, fecha_resolucion, estado)
VALUES
    (3, 5, 'Revisión de aire acondicionado', '2025-04-01', NULL, 'En proceso'),
    (6, 5, 'Arreglo de grifo en baño', '2025-03-25', '2025-03-27', 'Resuelto');

CREATE TABLE facturas (
    id_factura INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    fecha_emision DATE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL, -- Ej. "Tarjeta", "Efectivo", "Transferencia"
    CONSTRAINT fk_factura_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva)
);

INSERT INTO facturas (id_reserva, fecha_emision, total, metodo_pago)
VALUES
    (1, '2025-04-17', 300.00, 'Tarjeta'),
    (3, '2025-06-12', 250.00, 'Efectivo');

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Se recomienda encriptar (p. ej. hash con bcrypt)
    activo BOOLEAN NOT NULL DEFAULT 1,
    CONSTRAINT fk_usuario_empleado FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
);

-- Ejemplos: las contraseñas son en texto plano solo para la demo, en producción irían hasheadas.
INSERT INTO usuarios (id_empleado, username, password, activo)
VALUES
    (1, 'ana_recepcion', '1234', 1),
    (2, 'carlos_camarero', 'abcd', 1),
    (8, 'ricardo_gerente', 'admin', 1);
