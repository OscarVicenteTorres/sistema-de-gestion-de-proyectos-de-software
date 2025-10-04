-- ====================================================
-- BASE DE DATOS: GESTOR DE PROYECTOS
-- Versión actualizada con gestión completa de usuarios
-- ====================================================

-- ====================================================
-- 1) CREAR BASE DE DATOS
-- ====================================================
DROP DATABASE IF EXISTS prueba_proyectos;
CREATE DATABASE prueba_proyectos
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

USE prueba_proyectos;

-- ====================================================
-- 2) ROLES Y USUARIOS
-- ====================================================

CREATE TABLE roles (
  id_rol TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
  id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NULL,
  correo VARCHAR(100) NOT NULL UNIQUE,
  documento VARCHAR(20) NULL,
  tipo_documento VARCHAR(20) NULL,
  telefono VARCHAR(20) NULL,
  area_trabajo VARCHAR(100) NULL,
  fecha_inicio DATE NULL,
  tecnologias TEXT NULL,
  contrasena VARCHAR(255) NOT NULL,  -- se guarda hasheada
  id_rol TINYINT UNSIGNED NOT NULL,
  activo BOOLEAN DEFAULT TRUE,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

-- ====================================================
-- 3) CONTROL DE ASISTENCIA
-- ====================================================

CREATE TABLE asistencias (
  id_asistencia INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  hora_entrada TIME NULL,
  hora_salida TIME NULL,
  estado ENUM('Presente','Tarde','Ausente') DEFAULT 'Presente',
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE justificaciones (
  id_justificacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_asistencia INT UNSIGNED NOT NULL,
  motivo TEXT NOT NULL,
  nueva_fecha DATE NULL, -- si propone reprogramación
  estado ENUM('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente',
  fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_asistencia) REFERENCES asistencias(id_asistencia)
);

-- ====================================================
-- 4) PROYECTOS Y TAREAS
-- ====================================================

CREATE TABLE proyectos (
  id_proyecto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NULL,
  estado VARCHAR(50) DEFAULT 'Pendiente',
  area VARCHAR(150) NULL,
  porcentaje_avance TINYINT DEFAULT 0,
  cliente VARCHAR(200) NULL,
  recursos VARCHAR(200) NULL,
  tecnologias VARCHAR(200) NULL,
  id_usuario_creador INT UNSIGNED NOT NULL,
  FOREIGN KEY (id_usuario_creador) REFERENCES usuarios(id_usuario)
);

CREATE TABLE tareas (
  id_tarea INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_proyecto INT UNSIGNED NOT NULL,
  id_usuario INT UNSIGNED NOT NULL,
  titulo VARCHAR(100) NOT NULL,
  descripcion TEXT,
  fecha_limite DATE NULL,
  estado VARCHAR(50) DEFAULT 'Pendiente',
  porcentaje_avance TINYINT DEFAULT 0,
  FOREIGN KEY (id_proyecto) REFERENCES proyectos(id_proyecto) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- ====================================================
-- 5) NOTIFICACIONES
-- ====================================================

CREATE TABLE notificaciones (
  id_notificacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  mensaje VARCHAR(255) NOT NULL,
  tipo ENUM('Tarea','Proyecto','Asistencia') NOT NULL,
  leida BOOLEAN DEFAULT FALSE,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- ====================================================
-- 6) DATOS DE PRUEBA
-- ====================================================

-- Insertar roles básicos
INSERT INTO roles (nombre) VALUES 
  ('Admin'),
  ('Desarrollador'),
  ('Gestor de Proyecto');

-- Insertar usuarios de prueba con datos completos
-- NOTA: Las contraseñas están correctamente hasheadas con password_hash() de PHP
-- Contraseña original para todos: "123456"
-- Hash generado: $2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u

INSERT INTO usuarios (nombre, apellido, correo, documento, tipo_documento, telefono, area_trabajo, fecha_inicio, tecnologias, contrasena, id_rol, activo) VALUES 
  -- Usuario Admin
  ('Administrador', 'Sistema', 'admin@empresa.com', '12345678', 'DNI', '999888777', 'Administración', '2024-01-01', 'PHP, MySQL, JavaScript', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 1, TRUE),
  
  -- Usuario Desarrollador  
  ('Juan', 'Pérez', 'juan.perez@empresa.com', '87654321', 'DNI', '999777666', 'Frontend', '2024-02-01', 'React, Vue, CSS', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 2, TRUE),
  
  -- Usuario Gestor de Proyecto
  ('María', 'García', 'maria.garcia@empresa.com', '11223344', 'DNI', '999666555', 'Gerencia', '2024-01-15', 'Project Management, Scrum', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 3, TRUE),
  
  -- Desarrollador adicional
  ('Carlos', 'López', 'carlos.lopez@empresa.com', '44332211', 'DNI', '999555444', 'Backend', '2024-03-01', 'PHP, Python, Node.js', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 2, TRUE),
  
  -- Usuario bloqueado para pruebas
  ('Pedro', 'Martínez', 'pedro.martinez@empresa.com', '55667788', 'DNI', '999444333', 'Frontend', '2024-02-15', 'Angular, TypeScript', '$2y$10$cQ7f1E/9dQtDXETFmsGDKeCY2eRxwSlnC2CY5ky7ub03CccQfr35u', 2, FALSE),
  
  -- Usuario Personal
  ('Pedro Joaquin', 'Adriansen Flores', 'pedroadriansen@gmail.com', '60771494', 'DNI', '908061691', 'Frontend', '2024-10-01', 'Vue, CSS', '$2y$10$lodp/loabveGfC43m83YFOMf1F/2FvzoKHtgNcr25CmOJ8.Ul6kFi', 2, TRUE);

-- ====================================================
-- 7) DATOS DE EJEMPLO ADICIONALES
-- ====================================================

-- Proyecto de ejemplo
INSERT INTO proyectos (nombre, descripcion, fecha_inicio, fecha_fin, estado, area, porcentaje_avance, id_usuario_creador) VALUES 
  ('Sistema de Gestión Web', 'Desarrollo de aplicación web para gestión de proyectos y tareas', '2024-10-01', '2024-12-31', 'En desarrollo', 'Frontend', 25, 1);

-- Tareas de ejemplo
INSERT INTO tareas (id_proyecto, id_usuario, titulo, descripcion, fecha_limite, estado, porcentaje_avance) VALUES 
  (1, 2, 'Diseño de Base de Datos', 'Crear el esquema de base de datos completo', '2024-10-15', 'Completado', 100),
  (1, 2, 'Desarrollo del Sistema de Login', 'Implementar autenticación y autorización', '2024-10-30', 'En desarrollo', 60),
  (1, 4, 'Interfaz de Usuario', 'Diseñar y desarrollar las vistas principales', '2024-11-15', 'Pendiente', 0);

-- ====================================================
-- 8) USUARIOS DE PRUEBA ALTERNATIVOS (SIN HASH)
-- ====================================================
-- NOTA: Estos usuarios tienen contraseñas SIN hashear para pruebas rápidas
-- SOLO para desarrollo - NO usar en producción

/*
-- Descomenta estas líneas si quieres probar sin hash de contraseñas
-- Tendrás que modificar el modelo Usuario para quitar password_verify()

INSERT INTO usuarios (nombre, apellido, correo, documento, tipo_documento, telefono, area_trabajo, fecha_inicio, tecnologias, contrasena, id_rol, activo) VALUES 
  ('Admin', 'Test', 'admin@test.com', '11111111', 'DNI', '999999999', 'Testing', '2024-01-01', 'Testing', '123456', 1, TRUE),
  ('Dev', 'Test', 'dev@test.com', '22222222', 'DNI', '888888888', 'Testing', '2024-01-01', 'Testing', '123456', 2, TRUE);
*/

-- ====================================================
-- FIN DEL SCRIPT
-- ====================================================
-- Para ejecutar este script:
-- 1. Abre phpMyAdmin: http://localhost/phpmyadmin
-- 2. Selecciona la pestaña "SQL"
-- 3. Pega todo este script
-- 4. Haz clic en "Continuar"
-- ====================================================
-- Usuarios de prueba:
-- Admin: admin@empresa.com / 123456
-- Desarrollador: juan.perez@empresa.com / 123456
-- Gestor: maria.garcia@empresa.com / 123456
-- ====================================================