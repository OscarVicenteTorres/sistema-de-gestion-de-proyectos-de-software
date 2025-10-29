-- Migración: permitir NULL en tareas.id_usuario y cambiar FK a ON DELETE SET NULL
-- Ejecutar en entorno de pruebas antes de producción.

ALTER TABLE tareas
    MODIFY id_usuario int(10) UNSIGNED NULL;

-- El nombre de la FK puede variar; en el dump original estaba como `tareas_ibfk_2`
ALTER TABLE tareas
    DROP FOREIGN KEY IF EXISTS tareas_ibfk_2;

ALTER TABLE tareas
    ADD CONSTRAINT tareas_ibfk_2 FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL;
