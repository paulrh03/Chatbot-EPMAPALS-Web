-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 15-01-2026 a las 17:06:07
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u475379554_epmapals_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_bot`
--

CREATE TABLE `auditoria_bot` (
  `id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `accion` varchar(50) NOT NULL COMMENT 'Ej: LOGIN, MENU, SALDO',
  `detalle` text DEFAULT NULL COMMENT 'Descripción de lo que pasó',
  `mensaje_usuario` text DEFAULT NULL COMMENT 'Lo que escribió el usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria_bot`
--

INSERT INTO `auditoria_bot` (`id`, `fecha`, `cedula`, `nombre`, `accion`, `detalle`, `mensaje_usuario`) VALUES
(246, '2025-12-22 18:35:27', '0929917409', 'Luis Paul', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(247, '2025-12-22 18:46:47', '0929917409', 'Luis Paul', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(248, '2025-12-22 18:47:01', '0929917409', 'Luis Paul', 'INFO_REQUISITOS', 'Consultó: 📄 Guía Nueva con Medidor', '📑 requisitos para solicitar una guía nueva con medidor'),
(249, '2025-12-22 18:49:34', '0929917409', 'Luis Paul', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(252, '2025-12-22 18:52:53', '0929917409', 'Luis Paul', 'MENU_REQUISITOS', 'Usuario solicitó menú de requisitos', '🧾 requisitos de trámites'),
(253, '2025-12-22 18:53:02', '0929917409', 'Luis Paul', 'INFO_REQUISITOS', 'Consultó: 👤 Cambio de Titular (Compra/Venta)', '👤 cambio de titular (compra/venta)'),
(254, '2025-12-22 18:53:25', '0929917409', 'Luis Paul', 'MENU_REQUISITOS', 'Usuario solicitó menú de requisitos', '🧾 ver otros trámites'),
(255, '2025-12-22 18:53:30', '0929917409', 'Luis Paul', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(256, '2025-12-22 19:01:38', '0929917402', 'Mata Reyes', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0929917402'),
(257, '2025-12-22 19:01:40', '0929917402', 'Mata Reyes', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(258, '2025-12-22 19:01:42', '0929917402', 'Mata Reyes', 'MENU_REQUISITOS', 'Usuario solicitó menú de requisitos', '🧾 requisitos de trámites'),
(259, '2025-12-22 19:01:44', '0929917402', 'Mata Reyes', 'INFO_REQUISITOS', 'Consultó: 📄 Guía Nueva con Medidor', '📄 guía nueva con medidor'),
(260, '2025-12-22 19:02:08', '0929917402', 'Mata Reyes', 'MENU_REQUISITOS', 'Usuario solicitó menú de requisitos', '🧾 ver otros trámites'),
(261, '2025-12-22 19:02:10', '0929917402', 'Mata Reyes', 'INFO_REQUISITOS', 'Consultó: 👤 Cambio de Titular (Compra/Venta)', '👤 cambio de titular'),
(262, '2025-12-22 19:02:19', '0929917402', 'Mata Reyes', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(263, '2025-12-22 19:02:24', '0929917402', 'Mata Reyes', 'LIMPIEZA_INICIO', 'Inició solicitud de limpieza alcantarillado', '🕳️ solicitud de limpieza alcantarillado'),
(264, '2025-12-22 19:02:26', '0929917402', 'Mata Reyes', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(265, '2025-12-22 19:02:30', '0929917402', 'Mata Reyes', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', 'menu'),
(266, '2025-12-22 19:02:35', '0929917402', 'Mata Reyes', 'CONSULTA_ESTADO_LIMP', 'Usuario revisó estado limpieza', '🕳️ estado de limpieza de alcantarillado'),
(267, '2025-12-22 19:02:40', '0929917402', 'Mata Reyes', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(268, '2025-12-22 19:02:46', '0929917402', 'Mata Reyes', 'CONSULTA_ESTADO_FUGA', 'Usuario revisó estado fugas', '🚰 estado de reporte de fugas'),
(269, '2025-12-22 19:02:50', '0929917402', 'Mata Reyes', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(270, '2025-12-24 18:57:00', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0955385653'),
(271, '2025-12-24 18:57:03', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(272, '2025-12-24 18:57:25', '0955385653', 'Gianella', 'FUGA_INICIO', 'Inició reporte tipo: Fuga en Calle', '🚰 fuga en la calle'),
(273, '2025-12-24 18:58:28', '0955385653', 'Gianella', 'REPORTE_FUGA_FIN', 'CONFIRMADO | Tipo: calle | Tel: 0967263350 | Ref: Estación línea 108 | Prob: Desde la 12 de la tarde sale mucha agua', '🚀 confirmar reporte'),
(274, '2025-12-24 18:58:49', '0955385653', 'Gianella', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(275, '2025-12-24 18:59:16', '0955385653', 'Gianella', 'CONSULTA_ESTADO_FUGA', 'Usuario revisó estado fugas', '🚰 estado de reporte de fugas'),
(276, '2025-12-24 18:59:36', '0955385653', 'Gianella', 'CONSULTA_ESTADO_FUGA', 'Usuario revisó estado fugas', 'rf-20251224-0001'),
(277, '2026-01-02 17:17:40', '0929917409', 'Luis Paul', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0929917409'),
(278, '2026-01-02 17:17:59', '0929917409', 'Luis Paul', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(279, '2026-01-02 17:18:14', '0929917409', 'Luis Paul', 'SALDO_SELECCION', 'Usuario seleccionó contrato', '1'),
(280, '2026-01-02 17:18:16', '0929917409', 'Luis Paul', 'CONSULTA_SALDO', 'Medidor: 200103 | Total: $27.00 | Pendientes: 2 meses', '💰 ver saldo pendiente'),
(281, '2026-01-02 17:18:20', '0929917409', 'Luis Paul', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(282, '2026-01-02 17:18:28', '0929917409', 'Luis Paul', 'FUGA_INICIO', 'Inició reporte tipo: Baja Presión', '⬇️ baja presión de agua'),
(283, '2026-01-02 17:18:58', '0929917409', 'Luis Paul', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(284, '2026-01-02 17:55:22', '0929917409', 'Luis Paul', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0929917409'),
(285, '2026-01-02 17:55:24', '0929917409', 'Luis Paul', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(286, '2026-01-02 17:55:37', '0929917409', 'Luis Paul', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(287, '2026-01-05 18:21:01', '0929917409', 'Luis Paul', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0929917409'),
(288, '2026-01-05 18:22:45', '0929917409', 'Luis Paul', 'LOGIN_RECHAZADO', 'Usuario NO aceptó los términos', '❌ rechazar y salir'),
(289, '2026-01-05 18:22:58', '0929917409', 'Luis Paul', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0929917409'),
(290, '2026-01-06 03:14:06', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0955385653'),
(291, '2026-01-06 03:14:08', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(292, '2026-01-06 03:14:19', '0955385653', 'Gianella', 'SALDO_SELECCION', 'Usuario seleccionó contrato', '1'),
(293, '2026-01-06 03:14:21', '0955385653', 'Gianella', 'CONSULTA_SALDO', 'Medidor: 200106 | Total: $33.00 | Pendientes: 2 meses', '💰 ver saldo pendiente'),
(294, '2026-01-07 02:39:07', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0955385653'),
(295, '2026-01-07 02:39:15', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(296, '2026-01-07 03:00:42', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0955385653'),
(297, '2026-01-07 03:00:44', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(298, '2026-01-07 03:01:06', '0955385653', 'Gianella', 'SALDO_SELECCION', 'Usuario seleccionó contrato', '1'),
(299, '2026-01-07 03:01:09', '0955385653', 'Gianella', 'CONSULTA_SALDO', 'Medidor: 200106 | Total: $33.00 | Pendientes: 2 meses', '💰 ver saldo pendiente'),
(300, '2026-01-07 03:06:15', '0955385653', 'Gianella', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(301, '2026-01-07 03:06:45', '0955385653', 'Gianella', 'FUGA_INICIO', 'Inició reporte tipo: Fuga en Calle', '🚰 fuga en la calle'),
(302, '2026-01-07 03:11:01', '0955385653', 'Gianella', 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', '🏠 menú principal'),
(303, '2026-01-07 03:11:26', '0955385653', 'Gianella', 'LIMPIEZA_INICIO', 'Inició solicitud de limpieza alcantarillado', 'necesito limpieza de alcantarillado'),
(304, '2026-01-07 03:11:45', '0955385653', 'Gianella', 'LIMPIEZA_DATO_TEL', 'Usuario ingresó teléfono', '0955385653'),
(305, '2026-01-07 03:11:57', '0955385653', 'Gianella', 'LIMPIEZA_DATO_REF', 'Usuario ingresó referencia visual', 'casa blanca'),
(306, '2026-01-07 03:12:05', '0955385653', 'Gianella', 'LIMPIEZA_DATO_FALLA', 'Usuario describió el problema', 'malos olores'),
(307, '2026-01-07 03:12:12', '0955385653', 'Gianella', 'LIMPIEZA_CONFIRM', 'ORDEN CREADA: LP-20260107-0001 | Falla: malos olores', '🚀 confirmar orden'),
(308, '2026-01-07 17:39:46', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0955385653'),
(309, '2026-01-07 17:39:53', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(310, '2026-01-07 18:04:16', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0955385653'),
(311, '2026-01-07 18:04:18', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(312, '2026-01-07 18:04:23', '0955385653', 'Gianella', 'MENU_REQUISITOS', 'Usuario solicitó menú de requisitos', '🧾 requisitos de trámites'),
(313, '2026-01-07 19:29:00', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0955385653'),
(314, '2026-01-07 19:29:04', '0955385653', 'Gianella', 'LOGIN_EXITOSO', 'Usuario aceptó términos', '✅ aceptar y continuar'),
(315, '2026-01-13 14:56:53', '0929917409', 'Luis Paul', 'LOGIN_EXITOSO', 'Usuario validado correctamente', '0929917409'),
(316, '2026-01-13 15:00:23', '0929917409', 'Luis Paul', 'LOGIN_EXITOSO', 'Usuario aceptó términos', 'si continuar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `limpiezas`
--

CREATE TABLE `limpiezas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Registrado',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `cedula` varchar(10) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `referencia` varchar(200) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `limpiezas`
--

INSERT INTO `limpiezas` (`id`, `codigo`, `estado`, `fecha`, `cedula`, `nombre`, `telefono`, `direccion`, `referencia`, `descripcion`) VALUES
(5, 'LP-20251106-0005', 'PENDIENTE', '2025-11-06 04:19:05', '0926984063', 'angel villacres', '0926984063', 'saaa', 'Parque 2 de mayo', 'Mucha obstrucción de basura'),
(6, 'LP-20251107-0006', 'PENDIENTE', '2025-11-07 00:19:33', '0978314773', 'Paulina Luisa Reyes Castro', '0967034636', 'Isidro Ayora,22 de noviembre', 'Barrio divino niño', 'Mucha obstrucción de basura'),
(7, 'LP-20251107-0007', 'PENDIENTE', '2025-11-07 03:18:47', '0926984063', 'felipe', '0926984054', 'prueba', 'frente al estadio', 'mucha basura '),
(13, 'LP-20251119-0013', 'PENDIENTE', '2025-11-19 15:28:13', '0929917402', 'Marco Reyes', '22222', 'Cdla. Mapasingue Este', 'frente al tia', 'esta sin tapa'),
(14, 'LP-20251119-0014', 'PENDIENTE', '2025-11-19 15:54:28', '0929917402', 'Mata Reyes', '3333', 'Cdla. Mapasingue Este', 'a lado de tuti', 'malos olores desde hace dias'),
(15, 'LP-20251119-0015', 'PENDIENTE', '2025-11-19 18:21:43', '0929917401', 'Paul Reyes', '3333333333', 'Cdla. Mapasingue Oeste', 'Barrio divino niño', 'malos olores sin tapa '),
(17, 'LP-20251122-0017', 'PENDIENTE', '2025-11-22 18:10:56', '0922222222', 'Pedro Naranjo', '4444444444', 'Los Ceibos 11', 'frente al estadio', 'malos olores desde hace dias'),
(18, 'LP-20251123-0018', 'PENDIENTE', '2025-11-23 18:50:53', '0913150801', 'Yadira Hinojosa ', '0993325857', 'Padre krisma y Guayaquil', 'frente a la farmacia cruz azul', 'Malos olores'),
(20, 'LP-20260107-0001', 'PENDIENTE', '2026-01-07 03:12:12', '0955385653', 'Gianella', '0955385653', 'La Alborada 3ra Etapa', 'casa blanca', 'malos olores');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medidores`
--

CREATE TABLE `medidores` (
  `numero_medidor` int(11) NOT NULL,
  `cedula` varchar(10) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `direccion` varchar(120) NOT NULL,
  `manzana` int(11) NOT NULL,
  `sector` tinyint(4) NOT NULL CHECK (`sector` between 1 and 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `medidores`
--

INSERT INTO `medidores` (`numero_medidor`, `cedula`, `nombre`, `direccion`, `manzana`, `sector`) VALUES
(200101, '0926984063', 'Angel Moises', 'Av. Quito 123', 2, 2),
(200102, '0926984063', 'Angel Moises', '10 de Agosto 456', 3, 2),
(200103, '0929917409', 'Luis Paul', 'Calle A y 10', 1, 1),
(200104, '0912345678', 'Juan Pérez', 'Av. 9 de Octubre 225', 4, 3),
(200105, '0956789012', 'María Gómez', 'Cdla. Kennedy Norte Mz 14', 1, 4),
(200106, '0955385653', 'Gianella', 'La Alborada 3ra Etapa', 5, 1),
(200107, '0913150801', 'Yadira Hinojosa ', 'Padre krisma y Guayaquil', 4, 4),
(200108, '0922222222', 'Pedro Naranjo', 'Los Ceibos 11', 8, 3),
(200109, '0929917404', 'Paola Almeida', 'Cdla. Urdesa Central', 9, 4),
(200110, '0966666666', 'David Romero', 'Cdla. Huancavilca', 6, 1),
(200111, '0977777777', 'Sofía Torres', 'Av. Principal 789', 2, 2),
(200112, '0988888888', 'Marco Troya', 'Cdla. Garzota 2', 3, 3),
(200113, '0929917094', 'karen Gabriela', 'Padre krisma y Guayaquil', 4, 4),
(200114, '0900000001', 'Alba Toala', 'Cdla. Alborada 4', 5, 1),
(200115, '0929917403', 'Fran Astudillo', 'Cdla. Martha de Roldós', 6, 2),
(200116, '0929917402', 'Mata Reyes', 'Cdla. Mapasingue Este', 7, 3),
(200117, '0929917401', 'Paul Reyes', 'Cdla. Mapasingue Oeste', 8, 4),
(200118, '0900000005', 'Carolina Vera', 'Cdla. Flor de Bastión', 9, 1),
(200119, '0900000006', 'Diego Salazar', 'Cdla. Monte Sinaí', 1, 2),
(200120, '0900000007', 'Daniela Carrión', 'Cdla. Vergeles', 2, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `meses2025`
--

CREATE TABLE `meses2025` (
  `mes` char(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `meses2025`
--

INSERT INTO `meses2025` (`mes`) VALUES
('2025-01'),
('2025-02'),
('2025-03'),
('2025-04'),
('2025-05'),
('2025-06'),
('2025-07'),
('2025-08'),
('2025-09'),
('2025-10'),
('2025-11'),
('2025-12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_fuga`
--

CREATE TABLE `reportes_fuga` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `tipo` enum('calle','medidor','baja_presion') NOT NULL DEFAULT 'calle',
  `estado` varchar(20) NOT NULL DEFAULT 'Registrado',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `cedula` varchar(10) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `referencia` varchar(200) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `numero_medidor` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reportes_fuga`
--

INSERT INTO `reportes_fuga` (`id`, `codigo`, `tipo`, `estado`, `fecha`, `cedula`, `nombre`, `telefono`, `direccion`, `referencia`, `descripcion`, `numero_medidor`) VALUES
(59, 'RF-20251121-0059', 'medidor', 'PENDIENTE', '2025-11-21 14:57:44', '0900000007', 'Daniela Carrión', '0993325858', 'Cdla. Vergeles', 'frente al parque dos de mayo diagonal a la framacia casa verde', 'Tubo roto', '200120'),
(60, 'RF-20251121-0060', 'calle', 'PENDIENTE', '2025-11-21 15:05:17', '0900000007', 'Daniela Carrión', '0993325858', 'Cdla. Vergeles', 'frente al parque dos de mayo diagonal a la framacia casa verde', 'Tubo roto', '200120'),
(62, 'RF-20251121-0062', 'baja_presion', 'PENDIENTE', '2025-11-21 15:11:49', '0900000007', 'Daniela Carrión', '0993325858', 'Cdla. Vergeles', 'frente al parque dos de mayo diagonal a la farmacia casa verde', 'Tubo roto', '200120'),
(63, 'RF-20251124-0063', 'calle', 'PENDIENTE', '2025-11-24 18:55:46', '0929917401', 'Paul Reyes', '7373773', 'Cdla. Mapasingue Oeste', 'Parque dos de mayo diagonal a la farmacia casa verde', 'Tubo roto', '200117'),
(64, 'RF-20251221-0001', 'calle', 'PENDIENTE', '2025-12-21 22:27:59', '0929917409', 'Luis Paul', '0993325858', 'Calle A y 10', 'Frente al tia', 'Se daño el tubo', NULL),
(65, 'RF-20251224-0001', 'calle', 'PENDIENTE', '2025-12-24 18:58:28', '0955385653', 'Gianella', '0967263350', 'La Alborada 3ra Etapa', 'Estación línea 108', 'Desde la 12 de la tarde sale mucha agua', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saldos`
--

CREATE TABLE `saldos` (
  `id` int(11) NOT NULL,
  `numero_medidor` int(11) NOT NULL,
  `mes` char(7) NOT NULL,
  `estado` enum('pendiente','pagado') NOT NULL,
  `valor_pendiente` decimal(10,2) DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `hora_pago` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `saldos`
--

INSERT INTO `saldos` (`id`, `numero_medidor`, `mes`, `estado`, `valor_pendiente`, `fecha_pago`, `hora_pago`) VALUES
(1, 200101, '2025-01', 'pagado', NULL, '2025-01-06', '08:35:01'),
(2, 200101, '2025-02', 'pagado', NULL, '2025-02-06', '08:35:01'),
(3, 200101, '2025-03', 'pagado', NULL, '2025-03-06', '08:35:01'),
(4, 200101, '2025-04', 'pagado', NULL, '2025-04-06', '08:35:01'),
(5, 200101, '2025-05', 'pagado', NULL, '2025-05-06', '08:35:01'),
(6, 200101, '2025-06', 'pagado', NULL, '2025-06-06', '08:35:01'),
(7, 200101, '2025-07', 'pagado', NULL, '2025-07-06', '08:35:01'),
(8, 200101, '2025-08', 'pagado', NULL, '2025-08-06', '08:35:01'),
(9, 200101, '2025-09', 'pagado', NULL, '2025-09-06', '08:35:01'),
(10, 200101, '2025-10', 'pagado', NULL, '2025-10-06', '08:35:01'),
(11, 200101, '2025-11', 'pendiente', 18.50, NULL, NULL),
(12, 200101, '2025-12', 'pendiente', 18.50, NULL, NULL),
(13, 200102, '2025-01', 'pagado', NULL, '2025-01-07', '08:35:02'),
(14, 200102, '2025-02', 'pagado', NULL, '2025-02-07', '08:35:02'),
(15, 200102, '2025-03', 'pagado', NULL, '2025-03-07', '08:35:02'),
(16, 200102, '2025-04', 'pagado', NULL, '2025-04-07', '08:35:02'),
(17, 200102, '2025-05', 'pagado', NULL, '2025-05-07', '08:35:02'),
(18, 200102, '2025-06', 'pagado', NULL, '2025-06-07', '08:35:02'),
(19, 200102, '2025-07', 'pagado', NULL, '2025-07-07', '08:35:02'),
(20, 200102, '2025-08', 'pagado', NULL, '2025-08-07', '08:35:02'),
(21, 200102, '2025-09', 'pagado', NULL, '2025-09-07', '08:35:02'),
(22, 200102, '2025-10', 'pagado', NULL, '2025-10-07', '08:35:02'),
(23, 200102, '2025-11', 'pendiente', 12.50, NULL, NULL),
(24, 200102, '2025-12', 'pendiente', 12.50, NULL, NULL),
(25, 200103, '2025-01', 'pagado', NULL, '2025-01-08', '08:35:03'),
(26, 200103, '2025-02', 'pagado', NULL, '2025-02-08', '08:35:03'),
(27, 200103, '2025-03', 'pagado', NULL, '2025-03-08', '08:35:03'),
(28, 200103, '2025-04', 'pagado', NULL, '2025-04-08', '08:35:03'),
(29, 200103, '2025-05', 'pagado', NULL, '2025-05-08', '08:35:03'),
(30, 200103, '2025-06', 'pagado', NULL, '2025-06-08', '08:35:03'),
(31, 200103, '2025-07', 'pagado', NULL, '2025-07-08', '08:35:03'),
(32, 200103, '2025-08', 'pagado', NULL, '2025-08-08', '08:35:03'),
(33, 200103, '2025-09', 'pagado', NULL, '2025-09-08', '08:35:03'),
(34, 200103, '2025-10', 'pagado', NULL, '2025-10-08', '08:35:03'),
(35, 200103, '2025-11', 'pendiente', 13.50, NULL, NULL),
(36, 200103, '2025-12', 'pendiente', 13.50, NULL, NULL),
(37, 200104, '2025-01', 'pagado', NULL, '2025-01-09', '08:35:04'),
(38, 200104, '2025-02', 'pagado', NULL, '2025-02-09', '08:35:04'),
(39, 200104, '2025-03', 'pagado', NULL, '2025-03-09', '08:35:04'),
(40, 200104, '2025-04', 'pagado', NULL, '2025-04-09', '08:35:04'),
(41, 200104, '2025-05', 'pagado', NULL, '2025-05-09', '08:35:04'),
(42, 200104, '2025-06', 'pagado', NULL, '2025-06-09', '08:35:04'),
(43, 200104, '2025-07', 'pagado', NULL, '2025-07-09', '08:35:04'),
(44, 200104, '2025-08', 'pagado', NULL, '2025-08-09', '08:35:04'),
(45, 200104, '2025-09', 'pagado', NULL, '2025-09-09', '08:35:04'),
(46, 200104, '2025-10', 'pagado', NULL, '2025-10-09', '08:35:04'),
(47, 200104, '2025-11', 'pendiente', 14.50, NULL, NULL),
(48, 200104, '2025-12', 'pendiente', 14.50, NULL, NULL),
(49, 200105, '2025-01', 'pagado', NULL, '2025-01-10', '08:35:05'),
(50, 200105, '2025-02', 'pagado', NULL, '2025-02-10', '08:35:05'),
(51, 200105, '2025-03', 'pagado', NULL, '2025-03-10', '08:35:05'),
(52, 200105, '2025-04', 'pagado', NULL, '2025-04-10', '08:35:05'),
(53, 200105, '2025-05', 'pagado', NULL, '2025-05-10', '08:35:05'),
(54, 200105, '2025-06', 'pagado', NULL, '2025-06-10', '08:35:05'),
(55, 200105, '2025-07', 'pagado', NULL, '2025-07-10', '08:35:05'),
(56, 200105, '2025-08', 'pagado', NULL, '2025-08-10', '08:35:05'),
(57, 200105, '2025-09', 'pagado', NULL, '2025-09-10', '08:35:05'),
(58, 200105, '2025-10', 'pagado', NULL, '2025-10-10', '08:35:05'),
(59, 200105, '2025-11', 'pendiente', 15.50, NULL, NULL),
(60, 200105, '2025-12', 'pendiente', 15.50, NULL, NULL),
(61, 200106, '2025-01', 'pagado', NULL, '2025-01-11', '08:35:06'),
(62, 200106, '2025-02', 'pagado', NULL, '2025-02-11', '08:35:06'),
(63, 200106, '2025-03', 'pagado', NULL, '2025-03-11', '08:35:06'),
(64, 200106, '2025-04', 'pagado', NULL, '2025-04-11', '08:35:06'),
(65, 200106, '2025-05', 'pagado', NULL, '2025-05-11', '08:35:06'),
(66, 200106, '2025-06', 'pagado', NULL, '2025-06-11', '08:35:06'),
(67, 200106, '2025-07', 'pagado', NULL, '2025-07-11', '08:35:06'),
(68, 200106, '2025-08', 'pagado', NULL, '2025-08-11', '08:35:06'),
(69, 200106, '2025-09', 'pagado', NULL, '2025-09-11', '08:35:06'),
(70, 200106, '2025-10', 'pagado', NULL, '2025-10-11', '08:35:06'),
(71, 200106, '2025-11', 'pendiente', 16.50, NULL, NULL),
(72, 200106, '2025-12', 'pendiente', 16.50, NULL, NULL),
(73, 200107, '2025-01', 'pagado', NULL, '2025-01-12', '08:35:07'),
(74, 200107, '2025-02', 'pagado', NULL, '2025-02-12', '08:35:07'),
(75, 200107, '2025-03', 'pagado', NULL, '2025-03-12', '08:35:07'),
(76, 200107, '2025-04', 'pagado', NULL, '2025-04-12', '08:35:07'),
(77, 200107, '2025-05', 'pagado', NULL, '2025-05-12', '08:35:07'),
(78, 200107, '2025-06', 'pagado', NULL, '2025-06-12', '08:35:07'),
(79, 200107, '2025-07', 'pagado', NULL, '2025-07-12', '08:35:07'),
(80, 200107, '2025-08', 'pagado', NULL, '2025-08-12', '08:35:07'),
(81, 200107, '2025-09', 'pagado', NULL, '2025-09-12', '08:35:07'),
(82, 200107, '2025-10', 'pagado', NULL, '2025-10-12', '08:35:07'),
(83, 200107, '2025-11', 'pendiente', 17.50, NULL, NULL),
(84, 200107, '2025-12', 'pendiente', 17.50, NULL, NULL),
(85, 200108, '2025-01', 'pagado', NULL, '2025-01-13', '08:35:08'),
(86, 200108, '2025-02', 'pagado', NULL, '2025-02-13', '08:35:08'),
(87, 200108, '2025-03', 'pagado', NULL, '2025-03-13', '08:35:08'),
(88, 200108, '2025-04', 'pagado', NULL, '2025-04-13', '08:35:08'),
(89, 200108, '2025-05', 'pagado', NULL, '2025-05-13', '08:35:08'),
(90, 200108, '2025-06', 'pagado', NULL, '2025-06-13', '08:35:08'),
(91, 200108, '2025-07', 'pagado', NULL, '2025-07-13', '08:35:08'),
(92, 200108, '2025-08', 'pagado', NULL, '2025-08-13', '08:35:08'),
(93, 200108, '2025-09', 'pagado', NULL, '2025-09-13', '08:35:08'),
(94, 200108, '2025-10', 'pagado', NULL, '2025-10-13', '08:35:08'),
(95, 200108, '2025-11', 'pendiente', 18.50, NULL, NULL),
(96, 200108, '2025-12', 'pendiente', 18.50, NULL, NULL),
(97, 200109, '2025-01', 'pagado', NULL, '2025-01-14', '08:35:09'),
(98, 200109, '2025-02', 'pagado', NULL, '2025-02-14', '08:35:09'),
(99, 200109, '2025-03', 'pagado', NULL, '2025-03-14', '08:35:09'),
(100, 200109, '2025-04', 'pagado', NULL, '2025-04-14', '08:35:09'),
(101, 200109, '2025-05', 'pagado', NULL, '2025-05-14', '08:35:09'),
(102, 200109, '2025-06', 'pagado', NULL, '2025-06-14', '08:35:09'),
(103, 200109, '2025-07', 'pagado', NULL, '2025-07-14', '08:35:09'),
(104, 200109, '2025-08', 'pagado', NULL, '2025-08-14', '08:35:09'),
(105, 200109, '2025-09', 'pagado', NULL, '2025-09-14', '08:35:09'),
(106, 200109, '2025-10', 'pagado', NULL, '2025-10-14', '08:35:09'),
(107, 200109, '2025-11', 'pendiente', 12.50, NULL, NULL),
(108, 200109, '2025-12', 'pendiente', 12.50, NULL, NULL),
(109, 200110, '2025-01', 'pagado', NULL, '2025-01-15', '08:35:10'),
(110, 200110, '2025-02', 'pagado', NULL, '2025-02-15', '08:35:10'),
(111, 200110, '2025-03', 'pagado', NULL, '2025-03-15', '08:35:10'),
(112, 200110, '2025-04', 'pagado', NULL, '2025-04-15', '08:35:10'),
(113, 200110, '2025-05', 'pagado', NULL, '2025-05-15', '08:35:10'),
(114, 200110, '2025-06', 'pagado', NULL, '2025-06-15', '08:35:10'),
(115, 200110, '2025-07', 'pagado', NULL, '2025-07-15', '08:35:10'),
(116, 200110, '2025-08', 'pagado', NULL, '2025-08-15', '08:35:10'),
(117, 200110, '2025-09', 'pagado', NULL, '2025-09-15', '08:35:10'),
(118, 200110, '2025-10', 'pagado', NULL, '2025-10-15', '08:35:10'),
(119, 200110, '2025-11', 'pendiente', 13.50, NULL, NULL),
(120, 200110, '2025-12', 'pendiente', 13.50, NULL, NULL),
(121, 200111, '2025-01', 'pagado', NULL, '2025-01-16', '08:35:11'),
(122, 200111, '2025-02', 'pagado', NULL, '2025-02-16', '08:35:11'),
(123, 200111, '2025-03', 'pagado', NULL, '2025-03-16', '08:35:11'),
(124, 200111, '2025-04', 'pagado', NULL, '2025-04-16', '08:35:11'),
(125, 200111, '2025-05', 'pagado', NULL, '2025-05-16', '08:35:11'),
(126, 200111, '2025-06', 'pagado', NULL, '2025-06-16', '08:35:11'),
(127, 200111, '2025-07', 'pagado', NULL, '2025-07-16', '08:35:11'),
(128, 200111, '2025-08', 'pagado', NULL, '2025-08-16', '08:35:11'),
(129, 200111, '2025-09', 'pagado', NULL, '2025-09-16', '08:35:11'),
(130, 200111, '2025-10', 'pagado', NULL, '2025-10-16', '08:35:11'),
(131, 200111, '2025-11', 'pendiente', 14.50, NULL, NULL),
(132, 200111, '2025-12', 'pendiente', 14.50, NULL, NULL),
(133, 200112, '2025-01', 'pagado', NULL, '2025-01-17', '08:35:12'),
(134, 200112, '2025-02', 'pagado', NULL, '2025-02-17', '08:35:12'),
(135, 200112, '2025-03', 'pagado', NULL, '2025-03-17', '08:35:12'),
(136, 200112, '2025-04', 'pagado', NULL, '2025-04-17', '08:35:12'),
(137, 200112, '2025-05', 'pagado', NULL, '2025-05-17', '08:35:12'),
(138, 200112, '2025-06', 'pagado', NULL, '2025-06-17', '08:35:12'),
(139, 200112, '2025-07', 'pagado', NULL, '2025-07-17', '08:35:12'),
(140, 200112, '2025-08', 'pagado', NULL, '2025-08-17', '08:35:12'),
(141, 200112, '2025-09', 'pagado', NULL, '2025-09-17', '08:35:12'),
(142, 200112, '2025-10', 'pagado', NULL, '2025-10-17', '08:35:12'),
(143, 200112, '2025-11', 'pendiente', 15.50, NULL, NULL),
(144, 200112, '2025-12', 'pendiente', 15.50, NULL, NULL),
(145, 200113, '2025-01', 'pagado', NULL, '2025-01-18', '08:35:13'),
(146, 200113, '2025-02', 'pagado', NULL, '2025-02-18', '08:35:13'),
(147, 200113, '2025-03', 'pagado', NULL, '2025-03-18', '08:35:13'),
(148, 200113, '2025-04', 'pagado', NULL, '2025-04-18', '08:35:13'),
(149, 200113, '2025-05', 'pagado', NULL, '2025-05-18', '08:35:13'),
(150, 200113, '2025-06', 'pagado', NULL, '2025-06-18', '08:35:13'),
(151, 200113, '2025-07', 'pagado', NULL, '2025-07-18', '08:35:13'),
(152, 200113, '2025-08', 'pagado', NULL, '2025-08-18', '08:35:13'),
(153, 200113, '2025-09', 'pagado', NULL, '2025-09-18', '08:35:13'),
(154, 200113, '2025-10', 'pagado', NULL, '2025-10-18', '08:35:13'),
(155, 200113, '2025-11', 'pendiente', 16.50, NULL, NULL),
(156, 200113, '2025-12', 'pendiente', 16.50, NULL, NULL),
(157, 200114, '2025-01', 'pagado', NULL, '2025-01-19', '08:35:14'),
(158, 200114, '2025-02', 'pagado', NULL, '2025-02-19', '08:35:14'),
(159, 200114, '2025-03', 'pagado', NULL, '2025-03-19', '08:35:14'),
(160, 200114, '2025-04', 'pagado', NULL, '2025-04-19', '08:35:14'),
(161, 200114, '2025-05', 'pagado', NULL, '2025-05-19', '08:35:14'),
(162, 200114, '2025-06', 'pagado', NULL, '2025-06-19', '08:35:14'),
(163, 200114, '2025-07', 'pagado', NULL, '2025-07-19', '08:35:14'),
(164, 200114, '2025-08', 'pagado', NULL, '2025-08-19', '08:35:14'),
(165, 200114, '2025-09', 'pagado', NULL, '2025-09-19', '08:35:14'),
(166, 200114, '2025-10', 'pagado', NULL, '2025-10-19', '08:35:14'),
(167, 200114, '2025-11', 'pendiente', 17.50, NULL, NULL),
(168, 200114, '2025-12', 'pendiente', 17.50, NULL, NULL),
(169, 200115, '2025-01', 'pagado', NULL, '2025-01-20', '08:35:15'),
(170, 200115, '2025-02', 'pagado', NULL, '2025-02-20', '08:35:15'),
(171, 200115, '2025-03', 'pagado', NULL, '2025-03-20', '08:35:15'),
(172, 200115, '2025-04', 'pagado', NULL, '2025-04-20', '08:35:15'),
(173, 200115, '2025-05', 'pagado', NULL, '2025-05-20', '08:35:15'),
(174, 200115, '2025-06', 'pagado', NULL, '2025-06-20', '08:35:15'),
(175, 200115, '2025-07', 'pagado', NULL, '2025-07-20', '08:35:15'),
(176, 200115, '2025-08', 'pagado', NULL, '2025-08-20', '08:35:15'),
(177, 200115, '2025-09', 'pagado', NULL, '2025-09-20', '08:35:15'),
(178, 200115, '2025-10', 'pagado', NULL, '2025-10-20', '08:35:15'),
(179, 200115, '2025-11', 'pendiente', 18.50, NULL, NULL),
(180, 200115, '2025-12', 'pendiente', 18.50, NULL, NULL),
(181, 200116, '2025-01', 'pagado', NULL, '2025-01-21', '08:35:16'),
(182, 200116, '2025-02', 'pagado', NULL, '2025-02-21', '08:35:16'),
(183, 200116, '2025-03', 'pagado', NULL, '2025-03-21', '08:35:16'),
(184, 200116, '2025-04', 'pagado', NULL, '2025-04-21', '08:35:16'),
(185, 200116, '2025-05', 'pagado', NULL, '2025-05-21', '08:35:16'),
(186, 200116, '2025-06', 'pagado', NULL, '2025-06-21', '08:35:16'),
(187, 200116, '2025-07', 'pagado', NULL, '2025-07-21', '08:35:16'),
(188, 200116, '2025-08', 'pagado', NULL, '2025-08-21', '08:35:16'),
(189, 200116, '2025-09', 'pagado', NULL, '2025-09-21', '08:35:16'),
(190, 200116, '2025-10', 'pagado', NULL, '2025-10-21', '08:35:16'),
(191, 200116, '2025-11', 'pendiente', 12.50, NULL, NULL),
(192, 200116, '2025-12', 'pendiente', 12.50, NULL, NULL),
(193, 200117, '2025-01', 'pagado', NULL, '2025-01-22', '08:35:17'),
(194, 200117, '2025-02', 'pagado', NULL, '2025-02-22', '08:35:17'),
(195, 200117, '2025-03', 'pagado', NULL, '2025-03-22', '08:35:17'),
(196, 200117, '2025-04', 'pagado', NULL, '2025-04-22', '08:35:17'),
(197, 200117, '2025-05', 'pagado', NULL, '2025-05-22', '08:35:17'),
(198, 200117, '2025-06', 'pagado', NULL, '2025-06-22', '08:35:17'),
(199, 200117, '2025-07', 'pagado', NULL, '2025-07-22', '08:35:17'),
(200, 200117, '2025-08', 'pagado', NULL, '2025-08-22', '08:35:17'),
(201, 200117, '2025-09', 'pagado', NULL, '2025-09-22', '08:35:17'),
(202, 200117, '2025-10', 'pagado', NULL, '2025-10-22', '08:35:17'),
(203, 200117, '2025-11', 'pendiente', 13.50, NULL, NULL),
(204, 200117, '2025-12', 'pendiente', 13.50, NULL, NULL),
(205, 200118, '2025-01', 'pagado', NULL, '2025-01-23', '08:35:18'),
(206, 200118, '2025-02', 'pagado', NULL, '2025-02-23', '08:35:18'),
(207, 200118, '2025-03', 'pagado', NULL, '2025-03-23', '08:35:18'),
(208, 200118, '2025-04', 'pagado', NULL, '2025-04-23', '08:35:18'),
(209, 200118, '2025-05', 'pagado', NULL, '2025-05-23', '08:35:18'),
(210, 200118, '2025-06', 'pagado', NULL, '2025-06-23', '08:35:18'),
(211, 200118, '2025-07', 'pagado', NULL, '2025-07-23', '08:35:18'),
(212, 200118, '2025-08', 'pagado', NULL, '2025-08-23', '08:35:18'),
(213, 200118, '2025-09', 'pagado', NULL, '2025-09-23', '08:35:18'),
(214, 200118, '2025-10', 'pagado', NULL, '2025-10-23', '08:35:18'),
(215, 200118, '2025-11', 'pendiente', 14.50, NULL, NULL),
(216, 200118, '2025-12', 'pendiente', 14.50, NULL, NULL),
(217, 200119, '2025-01', 'pagado', NULL, '2025-01-24', '08:35:19'),
(218, 200119, '2025-02', 'pagado', NULL, '2025-02-24', '08:35:19'),
(219, 200119, '2025-03', 'pagado', NULL, '2025-03-24', '08:35:19'),
(220, 200119, '2025-04', 'pagado', NULL, '2025-04-24', '08:35:19'),
(221, 200119, '2025-05', 'pagado', NULL, '2025-05-24', '08:35:19'),
(222, 200119, '2025-06', 'pagado', NULL, '2025-06-24', '08:35:19'),
(223, 200119, '2025-07', 'pagado', NULL, '2025-07-24', '08:35:19'),
(224, 200119, '2025-08', 'pagado', NULL, '2025-08-24', '08:35:19'),
(225, 200119, '2025-09', 'pagado', NULL, '2025-09-24', '08:35:19'),
(226, 200119, '2025-10', 'pagado', NULL, '2025-10-24', '08:35:19'),
(227, 200119, '2025-11', 'pendiente', 15.50, NULL, NULL),
(228, 200119, '2025-12', 'pendiente', 15.50, NULL, NULL),
(229, 200120, '2025-01', 'pagado', NULL, '2025-01-05', '08:35:20'),
(230, 200120, '2025-02', 'pagado', NULL, '2025-02-05', '08:35:20'),
(231, 200120, '2025-03', 'pagado', NULL, '2025-03-05', '08:35:20'),
(232, 200120, '2025-04', 'pagado', NULL, '2025-04-05', '08:35:20'),
(233, 200120, '2025-05', 'pagado', NULL, '2025-05-05', '08:35:20'),
(234, 200120, '2025-06', 'pagado', NULL, '2025-06-05', '08:35:20'),
(235, 200120, '2025-07', 'pagado', NULL, '2025-07-05', '08:35:20'),
(236, 200120, '2025-08', 'pagado', NULL, '2025-08-05', '08:35:20'),
(237, 200120, '2025-09', 'pagado', NULL, '2025-09-05', '08:35:20'),
(238, 200120, '2025-10', 'pagado', NULL, '2025-10-05', '08:35:20'),
(239, 200120, '2025-11', 'pendiente', 16.50, NULL, NULL),
(240, 200120, '2025-12', 'pendiente', 16.50, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria_bot`
--
ALTER TABLE `auditoria_bot`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `limpiezas`
--
ALTER TABLE `limpiezas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_limp_codigo` (`codigo`);

--
-- Indices de la tabla `medidores`
--
ALTER TABLE `medidores`
  ADD PRIMARY KEY (`numero_medidor`);

--
-- Indices de la tabla `meses2025`
--
ALTER TABLE `meses2025`
  ADD PRIMARY KEY (`mes`);

--
-- Indices de la tabla `reportes_fuga`
--
ALTER TABLE `reportes_fuga`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_fuga_codigo` (`codigo`);

--
-- Indices de la tabla `saldos`
--
ALTER TABLE `saldos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_saldos_med` (`numero_medidor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria_bot`
--
ALTER TABLE `auditoria_bot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=317;

--
-- AUTO_INCREMENT de la tabla `limpiezas`
--
ALTER TABLE `limpiezas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `reportes_fuga`
--
ALTER TABLE `reportes_fuga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de la tabla `saldos`
--
ALTER TABLE `saldos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `saldos`
--
ALTER TABLE `saldos`
  ADD CONSTRAINT `fk_saldos_med` FOREIGN KEY (`numero_medidor`) REFERENCES `medidores` (`numero_medidor`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
