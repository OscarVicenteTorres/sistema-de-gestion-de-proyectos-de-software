<?php
require_once __DIR__ . "/../config/database.php";

class Usuario {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function login($correo, $contrasena) {
        $sql = "SELECT u.id_usuario, u.nombre, u.contrasena, r.nombre as rol
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.correo = :correo AND u.activo = 1
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            return $usuario;
        }
        return false;
    }

    public function obtenerTodos($filtros = []) { 
        $sql = "SELECT u.*, 
                       r.nombre as rol_nombre,
                       CASE 
                           WHEN u.activo = 1 THEN 'Activo'
                           ELSE 'Inactivo'
                       END as estado,
                       u.id_usuario
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id_rol";
        
        $condiciones = [];
        $parametros = [];

        
        if (!empty($filtros['area_trabajo'])) {
            // Usar búsqueda LIKE case-insensitive para evitar problemas de mayúsculas/minúsculas
            $condiciones[] = "LOWER(u.area_trabajo) LIKE :area_trabajo";
            $parametros[':area_trabajo'] = '%' . strtolower($filtros['area_trabajo']) . '%';
        }
        
        
        $condiciones[] = "u.activo = 1";
        
        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY u.nombre ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT u.*, 
                       r.nombre as rol_nombre,
                       u.id_rol as id_rol,
                       u.contrasena as contrasena_hash
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = :id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Agregar campo para mostrar contraseña (solo para vista)
        if ($usuario) {
            // Por seguridad, mostrar solo asteriscos
            $usuario['contrasena_visible'] = '********';
        }
        
        return $usuario;
    }

    public function crear($datos) {
        $sql = "INSERT INTO usuarios (nombre, apellido, documento, tipo_documento, correo, 
                telefono, area_trabajo, fecha_inicio, contrasena, tecnologias, id_rol, activo)
                VALUES (:nombre, :apellido, :documento, :tipo_documento, :correo,
                :telefono, :area_trabajo, :fecha_inicio, :contrasena, :tecnologias, :id_rol, :activo)";
        
        $stmt = $this->conn->prepare($sql);
        
        // Hash de la contraseña
        $contrasenaHash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido', $datos['apellido']);
        $stmt->bindParam(':documento', $datos['documento']);
        $stmt->bindParam(':tipo_documento', $datos['tipo_documento']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':area_trabajo', $datos['area_trabajo']);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':contrasena', $contrasenaHash);
        $stmt->bindParam(':tecnologias', $datos['tecnologias']);
        $stmt->bindParam(':id_rol', $datos['id_rol']);
        $stmt->bindParam(':activo', $datos['activo']);
        
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        // Construir la consulta dinámicamente según los datos proporcionados
        $campos = [];
        $valores = [];
        
        if (isset($datos['nombre'])) {
            $campos[] = "nombre = :nombre";
            $valores[':nombre'] = $datos['nombre'];
        }
        if (isset($datos['apellido'])) {
            $campos[] = "apellido = :apellido";
            $valores[':apellido'] = $datos['apellido'];
        }
        if (isset($datos['documento'])) {
            $campos[] = "documento = :documento";
            $valores[':documento'] = $datos['documento'];
        }
        if (isset($datos['tipo_documento'])) {
            $campos[] = "tipo_documento = :tipo_documento";
            $valores[':tipo_documento'] = $datos['tipo_documento'];
        }
        if (isset($datos['correo'])) {
            $campos[] = "correo = :correo";
            $valores[':correo'] = $datos['correo'];
        }
        if (isset($datos['telefono'])) {
            $campos[] = "telefono = :telefono";
            $valores[':telefono'] = $datos['telefono'];
        }
        if (isset($datos['area_trabajo'])) {
            $campos[] = "area_trabajo = :area_trabajo";
            $valores[':area_trabajo'] = $datos['area_trabajo'];
        }
        if (isset($datos['fecha_inicio'])) {
            $campos[] = "fecha_inicio = :fecha_inicio";
            $valores[':fecha_inicio'] = $datos['fecha_inicio'];
        }
        if (isset($datos['tecnologias'])) {
            $campos[] = "tecnologias = :tecnologias";
            $valores[':tecnologias'] = $datos['tecnologias'];
        }
        if (isset($datos['id_rol'])) {
            $campos[] = "id_rol = :id_rol";
            $valores[':id_rol'] = $datos['id_rol'];
        }
        if (isset($datos['contrasena'])) {
            $campos[] = "contrasena = :contrasena";
            $valores[':contrasena'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id_usuario = :id";
        $valores[':id'] = $id;
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($valores);
    }

    public function actualizarEstado($id, $estado) {
        $sql = "UPDATE usuarios SET activo = :estado WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function contarTareasAsignadas($id) {
        $sql = "SELECT COUNT(*) as total FROM tareas WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
