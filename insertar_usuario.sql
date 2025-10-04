-- ====================================================
-- INSERTAR NUEVO USUARIO CON HASH ESPECÍFICO
-- ====================================================

USE prueba_proyectos;

-- Insertar nuevo usuario con el hash proporcionado
-- Hash: $2y$10$lodp/loabveGfC43m83YFOMf1F/2FvzoKHtgNcr25CmOJ8.Ul6kFi

INSERT INTO usuarios (
    nombre, 
    apellido, 
    correo, 
    documento, 
    tipo_documento, 
    telefono, 
    area_trabajo, 
    fecha_inicio, 
    tecnologias, 
    contrasena, 
    id_rol, 
    activo
) VALUES (
    'Nuevo Usuario',                    -- nombre
    'Apellido Test',                     -- apellido
    'nuevo@test.com',                    -- correo (debe ser único)
    '12345678',                          -- documento
    'DNI',                               -- tipo_documento
    '999888777',                         -- telefono
    'Desarrollo',                        -- area_trabajo
    '2024-10-04',                        -- fecha_inicio (fecha actual)
    'PHP, JavaScript, MySQL',            -- tecnologias
    '$2y$10$lodp/loabveGfC43m83YFOMf1F/2FvzoKHtgNcr25CmOJ8.Ul6kFi', -- contrasena (hash)
    2,                                   -- id_rol (1=Admin, 2=Desarrollador, 3=Gestor)
    TRUE                                 -- activo
);

-- Verificar que se insertó correctamente
SELECT * FROM usuarios WHERE correo = 'nuevo@test.com';

-- ====================================================
-- NOTA: Modifica los datos según tus necesidades
-- ====================================================
-- Roles disponibles:
-- 1 = Admin
-- 2 = Desarrollador
-- 3 = Gestor de Proyecto
-- ====================================================
