-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-04-2025 a las 17:31:16
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pms_daniya_denia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` datetime DEFAULT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `estado` enum('Presente','Ausente','Permiso','Baja') DEFAULT 'Presente',
  `observaciones` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id_asistencia`, `id_empleado`, `fecha`, `hora_entrada`, `hora_salida`, `estado`, `observaciones`) VALUES
(1, 1, '2025-04-18', '2025-04-18 07:00:10', '2025-04-18 14:59:40', 'Presente', NULL),
(2, 2, '2025-04-18', '2025-04-18 15:23:00', '2025-04-18 23:02:00', 'Presente', 'Llegó 23 min tarde'),
(3, 1, '2025-04-19', '2025-04-19 03:30:22', '2025-04-19 03:30:33', 'Presente', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ausencias`
--

CREATE TABLE `ausencias` (
  `id_ausencia` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `tipo` enum('Vacaciones','Baja','Permiso') NOT NULL,
  `comentario` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ausencias`
--

INSERT INTO `ausencias` (`id_ausencia`, `id_empleado`, `fecha_inicio`, `fecha_fin`, `tipo`, `comentario`) VALUES
(1, 1, '2025-05-10', '2025-05-20', 'Vacaciones', 'Viaje personal'),
(2, 3, '2025-04-05', '2025-04-15', 'Baja', 'Intervención médica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id_cargo` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `descripcion` varchar(255) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `pagado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id_cargo`, `id_reserva`, `fecha`, `descripcion`, `importe`, `pagado`) VALUES
(1, 1, '2025-04-19 16:08:43', 'Tratamiento SPA - 1h', 60.00, 0),
(2, 1, '2025-04-19 16:08:43', 'Minibar (agua y snacks)', 15.50, 0),
(3, 1, '2025-04-19 16:08:43', 'Restaurante - Cena', 45.25, 0),
(4, 2, '2025-04-19 16:08:43', 'Almuerzo buffet', 25.00, 0),
(5, 2, '2025-04-19 16:08:43', 'Copa en el bar', 8.00, 0),
(6, 11, '2025-04-19 16:25:04', 'Almuerzo buffet', 30.00, 0),
(7, 11, '2025-04-19 16:25:13', 'Copa en el bar', 8.00, 0),
(8, 11, '2025-04-19 16:25:14', 'Minibar (agua y snacks)', 15.50, 0),
(9, 11, '2025-04-19 16:25:15', 'Restaurante - Cena', 45.25, 0),
(10, 11, '2025-04-19 16:25:15', 'Tratamiento SPA - 1h', 60.00, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `checkin_info`
--

CREATE TABLE `checkin_info` (
  `id_checkin` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `documento_url` varchar(255) DEFAULT NULL,
  `firma_url` varchar(255) DEFAULT NULL,
  `fecha_checkin` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `checkin_info`
--

INSERT INTO `checkin_info` (`id_checkin`, `id_reserva`, `documento_url`, `firma_url`, `fecha_checkin`) VALUES
(1, 3, '53945291X', 'ffff', '2025-04-12 00:21:28'),
(2, 8, '53945291X', 'ffff', '2025-04-12 00:22:00'),
(3, 10, '53945291X', 'ffff', '2025-04-19 16:11:18'),
(4, 11, '53945291X', 'ffff', '2025-04-19 16:17:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `estado_funnel` varchar(50) DEFAULT 'Nuevo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellidos`, `dni`, `email`, `telefono`, `direccion`, `activo`, `estado_funnel`) VALUES
(1, 'Sergio', 'López Alba', '53945291X', 'sergio.lopez@example.com', '600111333', 'C/ Palma, 5, Madrid', 1, 'Interesado'),
(2, 'Beatriz', 'Rodríguez Sanz', '12312312K', 'beatriz.rodriguez@example.com', '600444777', 'C/ Colón, 9, Valencia', 1, 'Nuevo'),
(3, 'Juan', 'Martínez Díaz', '99977755L', 'juan.martinez@example.com', '700888999', 'Av. Andalucía, 25, Sevilla', 0, 'Nuevo'),
(6, 'Martin', 'Stefanov Emilianov', '55667587657x', 'marotnkha@gmail.com', '43554654', 'adsdsa', 1, 'Nuevo'),
(9, 'Luis Jahir', 'Rodríguez Cedeño', '55667587657x', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'Nuevo'),
(10, 'Luis Jahir', 'Rodríguez Cedeño', '53945291X', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'Nuevo'),
(11, 'Luis Jahir', 'Rodríguez Cedeño', '55667587657x', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'Interesado'),
(12, 'Luis Jahir', 'Rodríguez Cedeño', '55667587657x', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'En Negociacion'),
(13, 'Luis Jahir', 'Rodríguez Cedeño', '55667587657x', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'Nuevo'),
(14, 'Luis Jahir', 'Rodríguez Cedeño', '55667587657x', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'Interesado'),
(15, 'Josue  ', 'Rodríguez Cedeño', '55667587657x', 'josue.rodriguez.1997@gmail.com', '65567567756', 'Calle Horno de los Apóstoles 8', 1, 'Nuevo'),
(17, 'Luis Jahir', 'Rodríguez Cedeño', '55667587657x', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'Cerrado'),
(18, 'Luis Jahir', 'Rodríguez Cedeño', '55667587657x', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, NULL),
(19, 'Luis Jahir', 'Rodríguez Cedeño', '53945291X', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'Nuevo'),
(20, 'Luis Jahir', 'Rodríguez Cedeño', '53945291X', 'luisilustraciones@gmail.com', '722152111', 'Calle Horno de los Apóstoles 8', 1, 'Interesado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id_departamento` int(11) NOT NULL,
  `nombre_departamento` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id_departamento`, `nombre_departamento`) VALUES
(1, 'Recepción'),
(2, 'Restaurante'),
(3, 'Mantenimiento'),
(4, 'Pisos'),
(5, 'Administración');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(50) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `id_departamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `nombre`, `apellidos`, `dni`, `telefono`, `email`, `direccion`, `fecha_contratacion`, `id_rol`, `id_departamento`) VALUES
(1, 'Ana', 'García López', '12345678A', '600111222', 'ana.garcia@daniyadenia.com', 'C/ Mar, 1, Denia', '2021-04-10', 1, 1),
(2, 'Carlos', 'Pérez Muñoz', '87654321B', '600333444', 'carlos.perez@daniyadenia.com', 'Av. Mediterráneo, 15, Denia', '2020-09-15', 2, 2),
(3, 'María', 'Sánchez Ruiz', '11122233C', '600555666', 'maria.sanchez@daniyadenia.com', 'C/ Estrella, 10, Denia', '2019-05-20', 3, 2),
(5, 'Lucía', 'Martín Torres', '99988877E', '600999000', 'lucia.martin@daniyadenia.com', 'C/ Dársena, 22, Denia', '2021-07-01', 5, 3),
(6, 'Pilar', 'Hernández Vives', '22233344F', '600444555', 'pilar.hernandez@daniyadenia.com', 'C/ Fénix, 4, Denia', '2017-11-10', 6, 4),
(7, 'Andrea', 'Martínez Rey', '55566677G', '600222111', 'andrea.martinez@daniyadenia.com', 'Av. Alicante, 33, Denia', '2022-01-15', 7, 4),
(8, 'Ricardo', 'Giménez Sáez', '77788899H', '600000123', 'ricardo.gimenez@daniyadenia.com', 'Plaza Mayor, 2, Denia', '2016-03-10', 8, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_factura` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `detalle` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalle`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_factura`, `id_reserva`, `fecha_emision`, `total`, `metodo_pago`, `detalle`) VALUES
(5, 3, '2025-04-11', 0.05, 'Efectivo', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id_habitacion` int(11) NOT NULL,
  `numero_habitacion` varchar(10) NOT NULL,
  `tipo_habitacion` varchar(50) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `piso` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id_habitacion`, `numero_habitacion`, `tipo_habitacion`, `capacidad`, `piso`, `estado`) VALUES
(1, '101', 'Doble', 2, 1, 'Disponible'),
(2, '102', 'Individual', 1, 1, 'Disponible'),
(3, '103', 'Doble', 2, 1, 'Mantenimiento'),
(4, '201', 'Suite', 4, 2, 'Disponible'),
(5, '202', 'Doble', 2, 2, 'Ocupada'),
(6, '203', 'Doble Superior', 2, 2, 'Disponible'),
(8, '222', '222', 222, 222, 'Disponible'),
(9, '222222', '22', 22222, 222, 'Disponible'),
(10, '69', 'sexual', 1, 1, 'Disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_empleado`
--

CREATE TABLE `horarios_empleado` (
  `id_horario` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `id_turno` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dia_semana` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios_empleado`
--

INSERT INTO `horarios_empleado` (`id_horario`, `id_empleado`, `id_turno`, `fecha_inicio`, `fecha_fin`, `dia_semana`) VALUES
(1, 1, 1, '2025-04-01', '2025-04-30', NULL),
(2, 2, 2, '2025-04-01', '2025-04-30', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimiento`
--

CREATE TABLE `mantenimiento` (
  `id_incidencia` int(11) NOT NULL,
  `id_habitacion` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `fecha_reporte` date NOT NULL,
  `fecha_resolucion` date DEFAULT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mantenimiento`
--

INSERT INTO `mantenimiento` (`id_incidencia`, `id_habitacion`, `id_empleado`, `descripcion`, `fecha_reporte`, `fecha_resolucion`, `estado`) VALUES
(2, 6, 5, 'Arreglo de grifo en baño', '2025-03-25', '2025-03-27', 'Resuelto'),
(6, 4, 2, 'se me ha roto la impresora', '2025-04-12', '2025-04-12', 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_habitacion` int(11) NOT NULL,
  `fecha_entrada` date NOT NULL,
  `fecha_salida` date NOT NULL,
  `estado_reserva` varchar(50) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `id_cliente`, `id_habitacion`, `fecha_entrada`, `fecha_salida`, `estado_reserva`, `total`) VALUES
(1, 1, 1, '2025-04-15', '2025-04-17', 'CheckOut', 180.00),
(2, 2, 5, '2025-05-01', '2025-05-05', 'Pendiente', 120.00),
(3, 3, 6, '2025-06-10', '2025-06-12', 'CheckOut', NULL),
(4, 1, 1, '2025-04-11', '2025-04-13', 'Cancelada', NULL),
(8, 1, 1, '2025-04-12', '2025-04-12', 'CheckOut', NULL),
(9, 1, 10, '2025-04-06', '2025-04-14', 'Pendiente', NULL),
(10, 1, 2, '2025-04-19', '2025-04-20', 'CheckIn', NULL),
(11, 1, 1, '2025-04-19', '2025-04-20', 'CheckIn', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Recepcionista'),
(2, 'Camarero'),
(3, 'Cocinero'),
(4, 'Jefe de Mantenimiento'),
(5, 'Mantenimiento'),
(6, 'Gobernanta'),
(7, 'Limpieza'),
(8, 'Gerente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_restaurante`
--

CREATE TABLE `servicios_restaurante` (
  `id_servicio` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `costo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios_restaurante`
--

INSERT INTO `servicios_restaurante` (`id_servicio`, `id_reserva`, `fecha`, `descripcion`, `costo`) VALUES
(1, 1, '2025-04-16', 'Cena en el restaurante principal', 50.00),
(2, 3, '2025-06-11', 'Almuerzo buffet', 30.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id_tarifa` int(11) NOT NULL,
  `nombre_tarifa` varchar(100) NOT NULL,
  `tipo_habitacion` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `temporada` varchar(50) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id_tarifa`, `nombre_tarifa`, `tipo_habitacion`, `precio`, `temporada`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 'Tarifa Estándar Baja', 'Doble', 80.00, 'Baja', '2025-01-01', '2025-03-31'),
(2, 'Tarifa Estándar Alta', 'Doble', 120.00, 'Alta', '2025-04-01', '2025-10-31'),
(3, 'Tarifa Suite Premium', 'Suite', 200.00, 'Alta', '2025-04-01', '2025-10-31'),
(4, 'Tarifa Individual Base', 'Individual', 60.00, 'Baja', '2025-01-01', '2025-12-31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_cargo`
--

CREATE TABLE `tipo_cargo` (
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_cargo`
--

INSERT INTO `tipo_cargo` (`id_tipo`, `nombre`, `precio`) VALUES
(1, 'Tratamiento SPA - 1h', 60.00),
(2, 'Minibar (agua y snacks)', 15.50),
(3, 'Restaurante - Cena', 45.25),
(4, 'Almuerzo buffet', 30.00),
(5, 'Copa en el bar', 8.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id_turno` int(11) NOT NULL,
  `nombre_turno` varchar(60) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `descanso_min` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id_turno`, `nombre_turno`, `hora_inicio`, `hora_fin`, `descanso_min`) VALUES
(1, 'Mañana', '07:00:00', '15:00:00', 30),
(2, 'Tarde', '15:00:00', '23:00:00', 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_empleado`, `username`, `password`, `activo`) VALUES
(1, 1, 'ana_recepcion', '1234', 1),
(2, 2, 'carlos_camarero', 'abcd', 1),
(3, 8, 'ricardo_gerente', 'admin', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `uk_ficha_unica` (`id_empleado`,`fecha`);

--
-- Indices de la tabla `ausencias`
--
ALTER TABLE `ausencias`
  ADD PRIMARY KEY (`id_ausencia`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id_cargo`),
  ADD KEY `id_reserva` (`id_reserva`);

--
-- Indices de la tabla `checkin_info`
--
ALTER TABLE `checkin_info`
  ADD PRIMARY KEY (`id_checkin`),
  ADD KEY `id_reserva` (`id_reserva`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id_departamento`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD KEY `fk_empleado_rol` (`id_rol`),
  ADD KEY `fk_empleado_departamento` (`id_departamento`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `fk_factura_reserva` (`id_reserva`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id_habitacion`);

--
-- Indices de la tabla `horarios_empleado`
--
ALTER TABLE `horarios_empleado`
  ADD PRIMARY KEY (`id_horario`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `id_turno` (`id_turno`);

--
-- Indices de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD PRIMARY KEY (`id_incidencia`),
  ADD KEY `fk_mantenimiento_habitacion` (`id_habitacion`),
  ADD KEY `fk_mantenimiento_empleado` (`id_empleado`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `fk_reserva_cliente` (`id_cliente`),
  ADD KEY `fk_reserva_habitacion` (`id_habitacion`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `servicios_restaurante`
--
ALTER TABLE `servicios_restaurante`
  ADD PRIMARY KEY (`id_servicio`),
  ADD KEY `fk_servrest_reserva` (`id_reserva`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id_tarifa`);

--
-- Indices de la tabla `tipo_cargo`
--
ALTER TABLE `tipo_cargo`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id_turno`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_usuario_empleado` (`id_empleado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ausencias`
--
ALTER TABLE `ausencias`
  MODIFY `id_ausencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `checkin_info`
--
ALTER TABLE `checkin_info`
  MODIFY `id_checkin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id_departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id_habitacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `horarios_empleado`
--
ALTER TABLE `horarios_empleado`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  MODIFY `id_incidencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `servicios_restaurante`
--
ALTER TABLE `servicios_restaurante`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id_tarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipo_cargo`
--
ALTER TABLE `tipo_cargo`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id_turno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`);

--
-- Filtros para la tabla `ausencias`
--
ALTER TABLE `ausencias`
  ADD CONSTRAINT `ausencias_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`);

--
-- Filtros para la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD CONSTRAINT `cargos_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`);

--
-- Filtros para la tabla `checkin_info`
--
ALTER TABLE `checkin_info`
  ADD CONSTRAINT `checkin_info_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`);

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `fk_empleado_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`),
  ADD CONSTRAINT `fk_empleado_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_factura_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`);

--
-- Filtros para la tabla `horarios_empleado`
--
ALTER TABLE `horarios_empleado`
  ADD CONSTRAINT `horarios_empleado_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `horarios_empleado_ibfk_2` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`);

--
-- Filtros para la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD CONSTRAINT `fk_mantenimiento_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `fk_mantenimiento_habitacion` FOREIGN KEY (`id_habitacion`) REFERENCES `habitaciones` (`id_habitacion`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reserva_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_reserva_habitacion` FOREIGN KEY (`id_habitacion`) REFERENCES `habitaciones` (`id_habitacion`);

--
-- Filtros para la tabla `servicios_restaurante`
--
ALTER TABLE `servicios_restaurante`
  ADD CONSTRAINT `fk_servrest_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
