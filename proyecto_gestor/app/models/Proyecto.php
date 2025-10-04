<?php
require_once __DIR__ . "/../config/database.php";

class Proyecto {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    /**
     * Obtener todos los proyectos con información de encargados
     */
    public function obtenerTodosConEncargados() {
        $sql = "SELECT 
                    p.id_proyecto,
                    p.nombre,
                    p.descripcion,
                    p.fecha_inicio,
                    p.fecha_fin,
                    p.estado,
                    p.area AS categoria,
                    p.porcentaje_avance,
                    GROUP_CONCAT(u.nombre SEPARATOR '<br>') AS encargados
                FROM proyectos p
                LEFT JOIN tareas t ON p.id_proyecto = t.id_proyecto
                LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
                GROUP BY p.id_proyecto
                ORDER BY p.fecha_inicio DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un proyecto por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT 
                    id_proyecto,
                    nombre,
                    descripcion,
                    fecha_inicio,
                    fecha_fin,
                    estado,
                    area AS categoria,
                    porcentaje_avance,
                    cliente,
                    recursos,
                    tecnologias,
                    id_usuario_creador
                FROM proyectos 
                WHERE id_proyecto = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo proyecto
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO proyectos (nombre, descripcion, fecha_inicio, fecha_fin, estado, area, porcentaje_avance, cliente, recursos, tecnologias, id_usuario_creador) 
                    VALUES (:nombre, :descripcion, :fecha_inicio, :fecha_fin, :estado, :area, :porcentaje_avance, :cliente, :recursos, :tecnologias, :id_usuario_creador)";
            
            $stmt = $this->conn->prepare($sql);
            
            // Preparar datos con valores por defecto
            $params = [
                ':nombre' => $datos['nombre'] ?? '',
                ':descripcion' => $datos['descripcion'] ?? '',
                ':fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                ':fecha_fin' => $datos['fecha_fin'] ?? null,
                ':estado' => $datos['estado'] ?? 'Pendiente',
                ':area' => $datos['categoria'] ?? null,
                ':porcentaje_avance' => $datos['porcentaje_avance'] ?? 0,
                ':cliente' => $datos['cliente'] ?? null,
                ':recursos' => $datos['recursos'] ?? null,
                ':tecnologias' => $datos['tecnologias'] ?? null,
                ':id_usuario_creador' => $datos['id_usuario_creador'] ?? null
            ];
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error al crear proyecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un proyecto
     */
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE proyectos 
                    SET nombre = :nombre, 
                        descripcion = :descripcion, 
                        fecha_inicio = :fecha_inicio, 
                        fecha_fin = :fecha_fin, 
                        estado = :estado, 
                        area = :area, 
                        porcentaje_avance = :porcentaje_avance,
                        cliente = :cliente,
                        recursos = :recursos,
                        tecnologias = :tecnologias
                    WHERE id_proyecto = :id";
            
            $stmt = $this->conn->prepare($sql);
            
            // Preparar parámetros
            $params = [
                ':nombre' => $datos['nombre'] ?? '',
                ':descripcion' => $datos['descripcion'] ?? '',
                ':fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                ':fecha_fin' => $datos['fecha_fin'] ?? null,
                ':estado' => $datos['estado'] ?? 'Pendiente',
                ':area' => $datos['categoria'] ?? null,
                ':porcentaje_avance' => $datos['porcentaje_avance'] ?? 0,
                ':cliente' => $datos['cliente'] ?? null,
                ':recursos' => $datos['recursos'] ?? null,
                ':tecnologias' => $datos['tecnologias'] ?? null,
                ':id' => $id
            ];
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error al actualizar proyecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un proyecto
     */
    public function eliminar($id) {
        try {
            // Primero eliminar las tareas asociadas
            $sqlTareas = "DELETE FROM tareas WHERE id_proyecto = :id";
            $stmtTareas = $this->conn->prepare($sqlTareas);
            $stmtTareas->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtTareas->execute();
            
            // Luego eliminar el proyecto
            $sql = "DELETE FROM proyectos WHERE id_proyecto = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar proyecto: " . $e->getMessage());
            return false;
        }
    }
}
