-- =========================================================
-- Base de datos: project_management (SCRIPT EN ESPAÑOL)
-- Descripciones y nombres en español para facilitar lectura
-- =========================================================

-- 0) Eliminar BD previa y crear nueva
DROP DATABASE IF EXISTS project_management;
CREATE DATABASE project_management
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
USE project_management;

-- =========================================================
-- 1) Catálogos básicos: roles y áreas
-- =========================================================
CREATE TABLE roles (
  id_rol TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE,
  descripcion VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE areas (
  id_area TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(50) NOT NULL UNIQUE,
  nombre_area VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Poblado inicial: roles y áreas
INSERT INTO roles (nombre, descripcion) VALUES
('monitor','Administrador / Jefe - gestiona proyectos, usuarios y exportaciones'),
('desarrollador','Desarrollador - ve tareas, reporta avances y registra asistencia');

INSERT INTO areas (clave, nombre_area) VALUES
('frontend','Frontend'),
('backend','Backend'),
('infra','Infraestructura');

-- =========================================================
-- 2) Usuarios (tabla en español)
-- =========================================================
CREATE TABLE usuarios (
  id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_rol TINYINT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  documento VARCHAR(50),
  correo VARCHAR(150) NOT NULL UNIQUE,
  telefono VARCHAR(30),
  area_trabajo VARCHAR(100),
  fecha_inicio DATE,
  contrasena_hash VARCHAR(255) NOT NULL,
  habilidades JSON NULL,
  estado ENUM('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_roles FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 3) Proyectos
-- =========================================================
CREATE TABLE proyectos (
  id_proyecto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  categoria VARCHAR(100),
  cliente_nombre VARCHAR(200),
  cliente_ruc VARCHAR(30),
  fecha_inicio DATE,
  fecha_fin DATE,
  descripcion TEXT,
  recursos JSON NULL,
  progreso TINYINT UNSIGNED NOT NULL DEFAULT 0, -- 0..100
  estado ENUM('planificacion','activo','completado','vencido','pausado') NOT NULL DEFAULT 'planificacion',
  creado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_fechas_proyecto CHECK (fecha_inicio IS NULL OR fecha_fin IS NULL OR fecha_inicio <= fecha_fin),
  CONSTRAINT fk_proyectos_creador FOREIGN KEY (creado_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 4) Tareas
-- =========================================================
CREATE TABLE tareas (
  id_tarea INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_proyecto INT UNSIGNED NOT NULL,
  titulo VARCHAR(200) NOT NULL,
  descripcion TEXT,
  id_area TINYINT UNSIGNED NULL,
  fecha_inicio DATE,
  fecha_fin DATE,
  progreso TINYINT UNSIGNED NOT NULL DEFAULT 0, -- 0..100
  estado ENUM('pendiente','investigacion','diseno','en_desarrollo','implementacion','completada','vencida') NOT NULL DEFAULT 'pendiente',
  creado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_fechas_tarea CHECK (fecha_inicio IS NULL OR fecha_fin IS NULL OR fecha_inicio <= fecha_fin),
  CONSTRAINT fk_tareas_proyecto FOREIGN KEY (id_proyecto) REFERENCES proyectos(id_proyecto) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_tareas_area FOREIGN KEY (id_area) REFERENCES areas(id_area) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_tareas_creador FOREIGN KEY (creado_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 5) Asignaciones de tarea (N:N)
-- =========================================================
CREATE TABLE asignaciones_tarea (
  id_asignacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tarea INT UNSIGNED NOT NULL,
  id_usuario INT UNSIGNED NOT NULL,
  asignado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  nota_rol VARCHAR(200),
  CONSTRAINT uq_tarea_usuario UNIQUE (id_tarea, id_usuario),
  CONSTRAINT fk_asig_tarea FOREIGN KEY (id_tarea) REFERENCES tareas(id_tarea) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_asig_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 6) Notificaciones
-- =========================================================
CREATE TABLE notificaciones (
  id_notificacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  titulo VARCHAR(200) NOT NULL,
  mensaje TEXT,
  leido TINYINT(1) DEFAULT 0,
  enlace VARCHAR(255),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 7) Asistencias (registro de entrada/salida)
-- =========================================================
CREATE TABLE asistencias (
  id_asistencia INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  ingreso DATETIME NOT NULL,
  salida DATETIME NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  nota VARCHAR(255),
  CONSTRAINT fk_asist_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 8) Registros de exportación (historial)
-- =========================================================
CREATE TABLE registros_exportacion (
  id_exportacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NULL,
  id_proyecto INT UNSIGNED NULL,
  formato ENUM('pdf','excel','csv','otro') NOT NULL,
  parametros JSON NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_export_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_export_proyecto FOREIGN KEY (id_proyecto) REFERENCES proyectos(id_proyecto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 9) Registros de auditoría (logs CRUD)
-- =========================================================
CREATE TABLE registros_auditoria (
  id_registro INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NULL,
  entidad VARCHAR(100),
  entidad_id INT,
  accion ENUM('crear','actualizar','eliminar') NOT NULL,
  detalles JSON NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Índices útiles
-- =========================================================
CREATE INDEX idx_proyecto_nombre ON proyectos(nombre);
CREATE INDEX idx_tarea_titulo ON tareas(titulo);
CREATE INDEX idx_usuario_correo ON usuarios(correo);
CREATE INDEX idx_asistencia_usuario_fecha ON asistencias(id_usuario, ingreso);

-- =========================================================
-- Procedimiento para recalcular progreso del proyecto (EN ESPAÑOL)
-- =========================================================
DELIMITER $$
CREATE PROCEDURE recalcular_progreso_proyecto(IN p_id_proyecto INT)
BEGIN
  DECLARE promedio_progreso DECIMAL(5,2);

  SELECT IFNULL(ROUND(AVG(progreso)),0) INTO promedio_progreso
    FROM tareas
    WHERE id_proyecto = p_id_proyecto;

  UPDATE proyectos
    SET progreso = LEAST(GREATEST(promedio_progreso,0),100),
        estado = CASE
          WHEN promedio_progreso >= 100 THEN 'completado'
          WHEN fecha_fin IS NOT NULL AND fecha_fin < CURDATE() AND promedio_progreso < 100 THEN 'vencido'
          WHEN fecha_inicio IS NOT NULL AND fecha_inicio <= CURDATE() AND promedio_progreso < 100 THEN 'activo'
          ELSE estado
        END,
        actualizado_en = CURRENT_TIMESTAMP
    WHERE id_proyecto = p_id_proyecto;
END$$
DELIMITER ;

-- =========================================================
-- Triggers: mantenimiento de progreso y auditoría (en ESPAÑOL)
-- =========================================================

DELIMITER $$
CREATE TRIGGER trg_despues_insert_tarea
AFTER INSERT ON tareas
FOR EACH ROW
BEGIN
  -- Recalcula progreso del proyecto relacionado
  CALL recalcular_progreso_proyecto(NEW.id_proyecto);

  -- Registro de auditoría
  INSERT INTO registros_auditoria (id_usuario, entidad, entidad_id, accion, detalles)
  VALUES (NEW.creado_por, 'tareas', NEW.id_tarea, 'crear', JSON_OBJECT('titulo', NEW.titulo, 'progreso', NEW.progreso));
END$$

CREATE TRIGGER trg_despues_update_tarea
AFTER UPDATE ON tareas
FOR EACH ROW
BEGIN
  -- Recalcula si cambió progreso, proyecto o fechas
  IF NEW.progreso <> OLD.progreso OR NEW.id_proyecto <> OLD.id_proyecto OR NEW.fecha_fin <> OLD.fecha_fin OR NEW.fecha_inicio <> OLD.fecha_inicio THEN
    CALL recalcular_progreso_proyecto(NEW.id_proyecto);
    IF OLD.id_proyecto IS NOT NULL AND OLD.id_proyecto <> NEW.id_proyecto THEN
      CALL recalcular_progreso_proyecto(OLD.id_proyecto);
    END IF;
  END IF;

  INSERT INTO registros_auditoria (id_usuario, entidad, entidad_id, accion, detalles)
  VALUES (NEW.creado_por, 'tareas', NEW.id_tarea, 'actualizar', JSON_OBJECT('progreso_anterior', OLD.progreso, 'progreso_nuevo', NEW.progreso));
END$$

CREATE TRIGGER trg_despues_delete_tarea
AFTER DELETE ON tareas
FOR EACH ROW
BEGIN
  CALL recalcular_progreso_proyecto(OLD.id_proyecto);
  INSERT INTO registros_auditoria (id_usuario, entidad, entidad_id, accion, detalles)
  VALUES (OLD.creado_por, 'tareas', OLD.id_tarea, 'eliminar', JSON_OBJECT('titulo', OLD.titulo));
END$$

CREATE TRIGGER trg_despues_insert_asistencia
AFTER INSERT ON asistencias
FOR EACH ROW
BEGIN
  -- Crear notificación de confirmación de asistencia
  INSERT INTO notificaciones (id_usuario, titulo, mensaje, creado_en)
  VALUES (NEW.id_usuario, 'Asistencia registrada', CONCAT('Ingreso registrado: ', DATE_FORMAT(NEW.ingreso, '%Y-%m-%d %H:%i:%s')), NEW.ingreso);

  -- Auditoría de asistencia
  INSERT INTO registros_auditoria (id_usuario, entidad, entidad_id, accion, detalles)
  VALUES (NEW.id_usuario, 'asistencias', NEW.id_asistencia, 'crear', JSON_OBJECT('ingreso', DATE_FORMAT(NEW.ingreso, '%Y-%m-%d %H:%i:%s')));
END$$
DELIMITER ;

-- =========================================================
-- Vista útil para dashboard: resumen por proyecto (EN ESPAÑOL)
-- =========================================================
CREATE OR REPLACE VIEW vw_resumen_proyecto AS
SELECT
  p.id_proyecto,
  p.nombre AS nombre_proyecto,
  p.categoria,
  p.cliente_nombre,
  p.fecha_inicio,
  p.fecha_fin,
  p.progreso,
  p.estado,
  (SELECT COUNT(*) FROM tareas t WHERE t.id_proyecto = p.id_proyecto) AS total_tareas,
  (SELECT COUNT(*) FROM tareas t WHERE t.id_proyecto = p.id_proyecto AND (t.fecha_fin < CURDATE() AND t.progreso < 100)) AS tareas_vencidas
FROM proyectos p;
