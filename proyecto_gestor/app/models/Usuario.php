<?php
require_once __DIR__ . "/../../app/config/database.php";

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

    public function obtenerTodos() {
        $sql = "SELECT u.*, r.nombre as rol_nombre,
                       u.id_usuario as id
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.activo DESC, u.nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT u.*, r.nombre as rol_nombre,
                       u.contrasena as contrasena_hash
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
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

    public function actualizarEstado($id, $estado) {
        $sql = "UPDATE usuarios SET activo = :estado WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
