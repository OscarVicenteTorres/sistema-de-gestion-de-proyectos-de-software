-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-10-2025 a las 21:10:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `prueba_proyectos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id_asistencia` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` enum('Presente','Tarde','Ausente') DEFAULT 'Presente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `justificaciones`
--

CREATE TABLE `justificaciones` (
  `id_justificacion` int(10) UNSIGNED NOT NULL,
  `id_asistencia` int(10) UNSIGNED NOT NULL,
  `motivo` text NOT NULL,
  `nueva_fecha` date DEFAULT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente',
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `justificaciones_tareas`
--

CREATE TABLE `justificaciones_tareas` (
  `id_justificacion_tarea` int(10) UNSIGNED NOT NULL,
  `id_tarea` int(10) UNSIGNED NOT NULL,
  `motivo` text NOT NULL COMMENT 'Razón por la cual solicita extensión',
  `nueva_fecha_limite` date NOT NULL COMMENT 'Nueva fecha límite propuesta',
  `estado` enum('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente' COMMENT 'Estado de la solicitud de justificación',
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Cuándo el desarrollador envió la solicitud',
  `fecha_respuesta` timestamp NULL DEFAULT NULL COMMENT 'Cuándo el admin respondió la solicitud',
  `respondido_por` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID del administrador que aprobó/rechazó',
  `comentarios_admin` text DEFAULT NULL COMMENT 'Comentarios adicionales del administrador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Justificaciones para extensión de tiempo en tareas (no asistencias)';

--
-- Volcado de datos para la tabla `justificaciones_tareas`
--

INSERT INTO `justificaciones_tareas` (`id_justificacion_tarea`, `id_tarea`, `motivo`, `nueva_fecha_limite`, `estado`, `fecha_solicitud`, `fecha_respuesta`, `respondido_por`, `comentarios_admin`) VALUES
(2, 4, 'Se requiere tiempo adicional para investigar mejores prácticas de UX/UI según feedback del cliente', '2025-10-27', 'Pendiente', '2025-10-06 18:56:02', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `tipo` enum('Tarea','Proyecto','Asistencia') NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE `proyectos` (
  `id_proyecto` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'Pendiente',
  `area` varchar(150) DEFAULT NULL,
  `porcentaje_avance` tinyint(4) DEFAULT 0,
  `cliente` varchar(200) DEFAULT NULL,
  `recursos` varchar(200) DEFAULT NULL,
  `tecnologias` varchar(200) DEFAULT NULL,
  `id_usuario_creador` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`id_proyecto`, `nombre`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`, `area`, `porcentaje_avance`, `cliente`, `recursos`, `tecnologias`, `id_usuario_creador`) VALUES
(5, 'Sistema de Gestión Web', 'hoal coasoadwefwfgwf', '2025-09-30', '2025-11-08', 'Pendiente', 'Tienda virtual', 0, '1223132143122 ', 'git', 'jnode', 1),
(8, 'werwe', 'wafe', '2025-09-28', '2025-11-07', 'Pendiente', 'Plataforma SaaS', 0, 'wfe', 'fe', 'fae', 1),
(10, 'fewfe', 'wafewaef', '2025-09-28', '2025-08-27', 'Pendiente', 'Landing page', 0, 'wefaf', 'efwaaewf', 'fewaawef', 1),
(11, 'hopoas', 'gdffg', '2025-10-02', '2025-10-29', 'Pendiente', 'Landing page', 0, 'fdsfssfdfweeer2334324242', 'sdgfdsgfsgsegr', 'esrggsdf', 5),
(12, 'botic app', 'bueno es un gestor de inventario para botica', '2024-10-04', '2026-02-15', 'En Desarrollo', 'Software de gestión', 60, '205428379284 gruvitec', 'no tenemos el enlace  :(', 'creo que es php mysql boostrap html y js mas css', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`) VALUES
(1, 'Admin'),
(2, 'Desarrollador'),
(3, 'Gestor de Proyecto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id_tarea` int(10) UNSIGNED NOT NULL,
  `id_proyecto` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `area_asignada` enum('Frontend','Backend','Infraestructura') DEFAULT 'Frontend' COMMENT 'Área de trabajo asignada para la tarea',
  `fecha_inicio` date DEFAULT NULL COMMENT 'Fecha de inicio planificada para la tarea',
  `fecha_limite` date DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'Pendiente',
  `porcentaje_avance` tinyint(4) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora de creación de la tarea'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id_tarea`, `id_proyecto`, `id_usuario`, `titulo`, `descripcion`, `area_asignada`, `fecha_inicio`, `fecha_limite`, `estado`, `porcentaje_avance`, `fecha_creacion`) VALUES
(4, 5, 2, 'Diseño de interfaz principal', 'Crear mockups y diseño de la pantalla principal del sistema', 'Frontend', '2025-10-07', '2025-10-20', 'En Progreso', 30, '2025-10-06 18:35:13'),
(5, 5, 4, 'API de autenticación', 'Desarrollar endpoints para login, logout y validación de tokens', 'Backend', '2025-10-07', '2025-10-15', 'Pendiente', 0, '2025-10-06 18:35:13'),
(6, 12, 11, 'Configuración de servidor', 'Configurar ambiente de producción y deployment', 'Infraestructura', '2025-10-08', '2025-10-25', 'Pendiente', 0, '2025-10-06 18:35:13'),
(7, 5, 2, 'Diseño de interfaz principal', 'Crear mockups y diseño de la pantalla principal del sistema', 'Frontend', '2025-10-07', '2025-10-20', 'En Progreso', 30, '2025-10-06 18:56:02'),
(8, 5, 4, 'API de autenticación', 'Desarrollar endpoints para login, logout y validación de tokens', 'Backend', '2025-10-07', '2025-10-15', 'Pendiente', 0, '2025-10-06 18:56:02'),
(9, 12, 11, 'Configuración de servidor', 'Configurar ambiente de producción y deployment', 'Infraestructura', '2025-10-08', '2025-10-25', 'Pendiente', 0, '2025-10-06 18:56:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `documento` varchar(20) DEFAULT NULL,
  `tipo_documento` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `area_trabajo` varchar(100) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `tecnologias` text DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `id_rol` tinyint(3) UNSIGNED NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `documento`, `tipo_documento`, `telefono`, `area_trabajo`, `fecha_inicio`, `tecnologias`, `contrasena`, `id_rol`, `activo`, `fecha_creacion`) VALUES
(1, 'Administrador Sistema', 'Sistema', 'admin@empresa.com', '12345678', 'DNI', '999888777', 'Administración', '2024-01-01', 'PHP, MySQL, JavaScript', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 1, 1, '2025-10-04 05:18:56'),
(2, 'Juan Pérez', 'Pérez', 'juan.perez@empresa.com', '87654321', 'DNI', '999777666', 'Frontend', '2024-02-01', 'React, Vue, CSS', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 2, 0, '2025-10-04 05:18:56'),
(3, 'María García', 'García', 'maria.garcia@empresa.com', '11223344', 'DNI', '999666555', 'Gerencia', '2024-01-15', 'Project Management, Scrum', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 3, 1, '2025-10-04 05:18:56'),
(4, 'Carlos López', 'López', 'carlos.lopez@empresa.com', '44332211', 'DNI', '999555444', 'Backend', '2024-03-01', 'PHP, Python, Node.js', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 2, 1, '2025-10-04 05:18:56'),
(5, 'Brayan', 'brayan', 'champibrayan14@gmail.com', '77021283', 'DNI', '946674643', 'Desarrollo', '2024-10-04', 'PHP, JavaScript, MySQL', '$2y$10$Kk2d3o6wh6RxFnAn/553cuXUmU/yTkQwgfisoAENpeHduRkRL48ni', 1, 1, '2025-10-04 14:12:08'),
(6, 'leo', 'villegas', 'admin@empresas.com', '1234567678', 'DNI', '94667467432', 'Gerencia', '2025-10-04', 'h', '$2y$10$Jiv2m3XjIv6TRnF/nMpOXOh/1rRqaF5n3d08dZWmuZSVK1.6jSrjS', 2, 1, '2025-10-04 14:22:07'),
(7, 'pedro', 'adrianse', 'pedro@senati.pe', '12131213423', 'DNI', '94667467432', 'Frontend', '2025-10-04', 'hola como estas este es pedrro sabe usar el phonk muy bien es bueno en fronennet y nose que mas decir', '$2y$10$YmHRdmDyfnV0ds8q1VWNKeAPoZpLMGDYnHne54wel.KzsQcIGlYHO', 2, 0, '2025-10-04 15:08:22'),
(9, 'Brayan', 'Pauccara', 'hola@gmail.com', '1234567678', 'DNI', '946674643dfhfhd', 'Backend', '2025-10-11', 'dssefsef', '$2y$10$bzRIju8Wxn.C6DlcTTz4n.tHUJJKf/qjZsW5tXNHCRnwF/7ArRhuy', 2, 1, '2025-10-04 18:21:26'),
(10, 'PEDRO', 'ADRIANSEN', 'PEDRO@GMAIL.COM', '1234567678', 'DNI', '946674643', 'Frontend', '2025-10-04', 'jnode', '$2y$10$l0DfOEqgKV70.QpJQkm3AOGoawF5NJzQiLtBoECn1FymM6g5mbrRK', 2, 0, '2025-10-04 18:48:46'),
(11, 'OSCAR', 'VICENTE TORRES', 'oscarvicente@gruvitec.com', '12345678', 'DNI', '990077356', 'Fullstack', '2025-10-04', '...', '$2y$10$c5IH9JOGvBMq.0zeFr.F0.JvvRuqwf0WpRShYGkyQOMevtjp2UFqi', 1, 1, '2025-10-04 18:54:55'),
(12, 'hola', 'fesfes', 'holas123@gmail.com', 'sefsfs', 'DNI', 'wdadada', 'Frontend', '2025-10-04', 'awdad', '$2y$10$1vo0g51w5d//VH1dwdmmxuO.i6fDWE3g6dDg7T.cHj7.3D.7Vrx0y', 2, 1, '2025-10-04 19:03:55'),
(13, 'Pedro ', 'adriansen flores', 'pedrojoaquin@gmail.com', '70601679', 'DNI', '904669214', 'Frontend', '2025-10-04', 'react css python php java script woordpres etc', '$2y$10$yU2Ou8mSyytauHX9ZzO2/OOJROHSyA99S.e0t/EBzI1Y9arRWKX0K', 1, 1, '2025-10-04 19:11:18');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `justificaciones`
--
ALTER TABLE `justificaciones`
  ADD PRIMARY KEY (`id_justificacion`),
  ADD KEY `id_asistencia` (`id_asistencia`);

--
-- Indices de la tabla `justificaciones_tareas`
--
ALTER TABLE `justificaciones_tareas`
  ADD PRIMARY KEY (`id_justificacion_tarea`),
  ADD KEY `id_tarea` (`id_tarea`),
  ADD KEY `respondido_por` (`respondido_por`),
  ADD KEY `idx_estado_justificacion` (`estado`),
  ADD KEY `idx_fecha_solicitud` (`fecha_solicitud`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id_proyecto`),
  ADD KEY `id_usuario_creador` (`id_usuario_creador`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id_tarea`),
  ADD KEY `id_proyecto` (`id_proyecto`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id_asistencia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `justificaciones`
--
ALTER TABLE `justificaciones`
  MODIFY `id_justificacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `justificaciones_tareas`
--
ALTER TABLE `justificaciones_tareas`
  MODIFY `id_justificacion_tarea` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id_proyecto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id_tarea` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `justificaciones`
--
ALTER TABLE `justificaciones`
  ADD CONSTRAINT `justificaciones_ibfk_1` FOREIGN KEY (`id_asistencia`) REFERENCES `asistencias` (`id_asistencia`);

--
-- Filtros para la tabla `justificaciones_tareas`
--
ALTER TABLE `justificaciones_tareas`
  ADD CONSTRAINT `justificaciones_tareas_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id_tarea`) ON DELETE CASCADE,
  ADD CONSTRAINT `justificaciones_tareas_ibfk_2` FOREIGN KEY (`respondido_por`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`id_usuario_creador`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`),
  ADD CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
