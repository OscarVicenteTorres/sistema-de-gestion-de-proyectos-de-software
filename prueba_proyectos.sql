-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-11-2025 a las 01:23:13
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

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id_asistencia`, `id_usuario`, `fecha`, `hora_entrada`, `hora_salida`, `estado`) VALUES
(1, 21, '2025-10-29', '09:29:28', '10:51:13', 'Presente'),
(2, 21, '2025-11-01', '12:05:38', '19:15:01', 'Presente');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_tareas`
--

CREATE TABLE `notas_tareas` (
  `id_nota` int(10) UNSIGNED NOT NULL,
  `id_tarea` int(10) UNSIGNED NOT NULL,
  `porcentaje_anterior` tinyint(4) DEFAULT 0 COMMENT 'Porcentaje anterior antes de esta actualización',
  `porcentaje_nuevo` tinyint(4) DEFAULT 0 COMMENT 'Nuevo porcentaje actualizado',
  `nota_desarrollador` text DEFAULT NULL COMMENT 'Nota/reporte del desarrollador sobre el avance',
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora en que se envió el avance'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de avances y cambios de porcentaje en tareas';

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
(12, 'botic app', 'bueno es un gestor de inventario para botica', '2024-10-04', '2026-02-15', 'En Desarrollo', 'Software de gestión', 60, '205428379284 gruvitec', 'no tenemos el enlace  :(', 'creo que es php mysql boostrap html y js mas css', 5),
(17, 'tienda virtual', 'este es un proyecto de e comerce tienda vitual', '2025-10-27', '2025-11-08', 'Pendiente', 'Tienda virtual', 0, '1223131432', 'git ', 'node .js', 5),
(18, 'pagina ', 'sera una pagina web que se puedam crear a base de todo lo indicado', '2025-10-28', '2025-11-08', 'Pendiente', 'Landing page', 0, '1223131432', 'git ', 'python php javascript', 5);

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
(2, 'Desarrollador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id_tarea` int(10) UNSIGNED NOT NULL,
  `id_proyecto` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
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
(6, 12, 13, 'Configuración de servidor', 'Configurar ambiente de producción y deployment', 'Frontend', '2025-10-08', '2025-10-25', 'Pendiente', 0, '2025-10-06 18:35:13'),
(21, 12, 5, 'crear api para backend', 'debes crear una api para listar los usuarios del sistema quiero que los resultados los bote en formato json para mayor seguridad', 'Backend', '2025-10-27', '2025-10-29', 'Pendiente', 0, '2025-10-27 17:06:29'),
(22, 18, 13, 'investigar', 'tienes que investigar que ase la pagina etc', 'Frontend', '2025-10-28', '2025-10-28', 'Pendiente', 0, '2025-10-28 16:14:05'),
(23, 12, 25, 'SDAD', 'DSADA', 'Frontend', '2025-11-01', '2025-11-18', 'Pendiente', 0, '2025-11-01 21:59:55'),
(24, 12, 13, 'HOLA', 'DWADA', 'Frontend', '2025-11-01', '2025-11-29', 'Pendiente', 0, '2025-11-01 22:05:53'),
(25, 17, 21, 'Configurar backend API', 'Crear endpoints REST para la tienda virtual en Node.js', 'Backend', '2025-10-27', '2025-11-05', 'Pendiente', 0, '2025-11-02 00:00:00');

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
(1, 'Administrador Sistema', 'Sistema', 'admin@empresa.com', '12345678', 'DNI', '999888777', 'Administración', '2024-01-01', 'PHP, MySQL, JavaScript', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 1, 0, '2025-10-04 05:18:56'),
(4, 'Carlos López', 'López', 'carlos.lopez@empresa.com', '44332211', 'DNI', '999555444', 'Backend', '2024-03-01', 'PHP, Python, Node.js', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 2, 1, '2025-10-04 05:18:56'),
(5, 'Brayan', 'brayan', 'champibrayan14@gmail.com', '77021283', 'DNI', '946674643', 'Backend\r\n', '2024-10-04', 'PHP, JavaScript, MySQL', '$2y$10$Kk2d3o6wh6RxFnAn/553cuXUmU/yTkQwgfisoAENpeHduRkRL48ni', 1, 1, '2025-10-04 14:12:08'),
(6, 'leonard', 'villegas', 'admin@empresas.com', '123456767823', 'DNI', '94667467432', 'Backend', '2025-10-04', 'h', '$2y$10$Jiv2m3XjIv6TRnF/nMpOXOh/1rRqaF5n3d08dZWmuZSVK1.6jSrjS', 1, 0, '2025-10-04 14:22:07'),
(11, 'OSCAR', 'VICENTE TORRES', 'oscarvicente@gruvitec.com', '12345678', 'DNI', '990077356', 'Fullstack', '2025-10-04', '...', '$2y$10$c5IH9JOGvBMq.0zeFr.F0.JvvRuqwf0WpRShYGkyQOMevtjp2UFqi', 1, 0, '2025-10-04 18:54:55'),
(13, 'Pedro ', 'adriansen flores', 'pedrojoaquin@gmail.com', '70601679', 'DNI', '904669214', 'Frontend', '2025-10-04', 'react css python php java script woordpres etc', '$2y$10$yU2Ou8mSyytauHX9ZzO2/OOJROHSyA99S.e0t/EBzI1Y9arRWKX0K', 1, 1, '2025-10-04 19:11:18'),
(17, 'leo', 'sar', 'usuario123@gmail.com', '77021283', 'DNI', '77021283', 'Frontend', '2025-10-28', 'react', '$2y$10$H0cb9ev1GZzTaMWcViyN2e.8bbuI07bos.0Wnn73wjvv65jVKC3C2', 1, 1, '2025-10-28 17:43:53'),
(19, 'hor', 'das', 'les@gmail.com', '2342342443', 'DNI', '97656473', 'Fullstack', '2025-10-28', 'sdadsad', '$2y$10$W5ThJy3ouXLnjWDVP0NADeOlvAGb1e01wt7lTiSzuMMqMEwOOojk.', 2, 0, '2025-10-28 17:49:45'),
(21, 'Brayan', 'Champi', 'holas123@gmail.com', '77021283', 'DNI', '23453453354', 'Backend', '2025-10-28', 'node .js', '$2y$10$hQ9EZVlC9R3SW7ffBmzBIO5rMx8mEWNjqVxC9GlnSOuWWLBlBzfhW', 2, 1, '2025-10-28 17:50:45'),
(25, 'Brayan', 'Champi', 'champibrayan14@gmai.com', '77021283', 'DNI', '23432432523', 'Frontend', '2025-10-28', 'node .js', '$2y$10$8OyxuLX3SG60kOkYC7ihReJMVFS5bw00UrBWPIwINqtiSs14EgB3a', 1, 1, '2025-10-28 17:52:10'),
(27, 'roanl', 'ser', 'champibraya@gmail.com', '23423423', 'DNI', '23424234', 'Frontend', '2025-10-28', 'react', '$2y$10$3mYZn2i8QAzodjiA5gtSLO1Kk5/148PBzVaEHQHMBCfhmcVQoXg46', 2, 0, '2025-10-28 18:20:21'),
(31, 'reaseee', 'asda', 'reas@gmail.com', '32466788', 'DNI', '23465487689', 'Frontend', '2025-10-28', 'react', '$2y$10$aXFDGSCCn4UCB3rsfOJwQOgKCnnuOld8wpjm7Ke.Y1N4hfwGVLWMm', 1, 1, '2025-10-28 18:36:56'),
(32, 'fads', 'ffdsa', 's@gmail.com', '3453535353', 'DNI', '34563465345', 'Frontend', '2025-10-28', 'react', '$2y$10$HEyL88h1uERgPCivu2ebTe4EaClPjvyxW7kImpPRilpzPf39VuMPW', 1, 0, '2025-10-28 18:41:43'),
(34, 'rest', 'dsada', 'e@gmail.com', '23424242', 'DNI', '2342422353', 'Frontend', '2025-10-28', 'react', '$2y$10$Blqi7Zp29u9iZ3r99wUd.Ohm4e4bMUmAagD2sOAqZ3pX/q./EO9f2', 1, 0, '2025-10-28 18:47:05');

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
-- Indices de la tabla `justificaciones_tareas`
--
ALTER TABLE `justificaciones_tareas`
  ADD PRIMARY KEY (`id_justificacion_tarea`),
  ADD KEY `id_tarea` (`id_tarea`),
  ADD KEY `respondido_por` (`respondido_por`),
  ADD KEY `idx_estado_justificacion` (`estado`),
  ADD KEY `idx_fecha_solicitud` (`fecha_solicitud`);

--
-- Indices de la tabla `notas_tareas`
--
ALTER TABLE `notas_tareas`
  ADD PRIMARY KEY (`id_nota`),
  ADD KEY `id_tarea` (`id_tarea`),
  ADD KEY `idx_fecha_envio` (`fecha_envio`);

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
  MODIFY `id_asistencia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `justificaciones_tareas`
--
ALTER TABLE `justificaciones_tareas`
  MODIFY `id_justificacion_tarea` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notas_tareas`
--
ALTER TABLE `notas_tareas`
  MODIFY `id_nota` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id_proyecto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id_tarea` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `justificaciones_tareas`
--
ALTER TABLE `justificaciones_tareas`
  ADD CONSTRAINT `justificaciones_tareas_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id_tarea`) ON DELETE CASCADE,
  ADD CONSTRAINT `justificaciones_tareas_ibfk_2` FOREIGN KEY (`respondido_por`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `notas_tareas`
--
ALTER TABLE `notas_tareas`
  ADD CONSTRAINT `notas_tareas_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id_tarea`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
