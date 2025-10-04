-- Script para agregar los nuevos campos a la tabla proyectos
-- Ejecuta esto si ya tienes la base de datos creada y solo quieres agregar los nuevos campos

USE prueba_proyectos;

-- Agregar columna cliente
ALTER TABLE proyectos 
ADD COLUMN cliente VARCHAR(200) NULL AFTER porcentaje_avance;

-- Agregar columna recursos
ALTER TABLE proyectos 
ADD COLUMN recursos VARCHAR(200) NULL AFTER cliente;

-- Agregar columna tecnologias
ALTER TABLE proyectos 
ADD COLUMN tecnologias VARCHAR(200) NULL AFTER recursos;

-- Verificar que los campos se agregaron correctamente
DESCRIBE proyectos;
