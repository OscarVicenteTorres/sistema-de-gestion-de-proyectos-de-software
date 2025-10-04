-- Script para actualizar la columna 'area' en la tabla proyectos
-- Ejecutar este script en phpMyAdmin o MySQL Workbench

USE prueba_proyectos;

-- Cambiar el tipo de columna de ENUM a VARCHAR para soportar las nuevas categorías
ALTER TABLE proyectos 
MODIFY COLUMN area VARCHAR(150) NULL;

-- Actualizar también el estado para incluir los nuevos valores
ALTER TABLE proyectos 
MODIFY COLUMN estado VARCHAR(50) DEFAULT 'Pendiente';

-- Actualizar el estado de las tareas para incluir los nuevos valores
ALTER TABLE tareas 
MODIFY COLUMN estado VARCHAR(50) DEFAULT 'Pendiente';

-- Verificar los cambios
DESCRIBE proyectos;
DESCRIBE tareas;
