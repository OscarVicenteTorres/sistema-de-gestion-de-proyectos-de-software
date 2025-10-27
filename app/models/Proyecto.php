<?php
require_once __DIR__ . "/../config/database.php";

class Proyecto
{

    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Obtener todos los proyectos con información de encargados
     */
    public function obtenerTodosConEncargados()
    {
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

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un proyecto por ID
     */
    public function obtenerPorId($id)
    {
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
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo proyecto
     */
    public function crear($datos)
    {
        try {
            $sql = "INSERT INTO proyectos (nombre, descripcion, fecha_inicio, fecha_fin, estado, area, porcentaje_avance, cliente, recursos, tecnologias, id_usuario_creador) 
                    VALUES (:nombre, :descripcion, :fecha_inicio, :fecha_fin, :estado, :area, :porcentaje_avance, :cliente, :recursos, :tecnologias, :id_usuario_creador)";

            $stmt = $this->db->prepare($sql);

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
    public function actualizar($id, $datos)
    {
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

            $stmt = $this->db->prepare($sql);

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
    public function eliminar($id)
    {
        try {
            // Primero eliminar las tareas asociadas
            $sqlTareas = "DELETE FROM tareas WHERE id_proyecto = :id";
            $stmtTareas = $this->db->prepare($sqlTareas);
            $stmtTareas->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtTareas->execute();

            // Luego eliminar el proyecto
            $sql = "DELETE FROM proyectos WHERE id_proyecto = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar proyecto: " . $e->getMessage());
            return false;
        }
    }


    public function obtenerProyectos()
    {
        $sql = "SELECT id, nombre, herramienta, estado, avance FROM proyectos";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function actualizarProgreso($id_proyecto)
    {
        try {
            // Calcular promedio de avance de todas las tareas del proyecto
            $sql = "SELECT AVG(porcentaje_avance) AS promedio FROM tareas WHERE id_proyecto = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id_proyecto]);
            $promedio = $stmt->fetchColumn();

            if ($promedio === null) $promedio = 0;

            // Determinar estado según el porcentaje
            $nuevoEstado = 'Pendiente';
            if ($promedio > 0 && $promedio < 100) {
                $nuevoEstado = 'Activo'; // O 'En Desarrollo' si prefieres
            } elseif ($promedio >= 100) {
                $nuevoEstado = 'Completado';
            }

            // Actualizar el proyecto
            $update = "UPDATE proyectos 
                   SET porcentaje_avance = :promedio, estado = :estado 
                   WHERE id_proyecto = :id";
            $stmt2 = $this->db->prepare($update);
            $stmt2->execute([
                ':promedio' => round($promedio, 2),
                ':estado' => $nuevoEstado,
                ':id' => $id_proyecto
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Error al actualizar progreso del proyecto: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerCompletados()
    {
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
            WHERE p.estado = 'Completado'
            GROUP BY p.id_proyecto
            ORDER BY p.fecha_fin DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    //   Obtener estadísticas generales de proyectos
    //  (CORREGIDO PARA CONTAR 'en_curso' CORRECTAMENTE)
    public function obtenerEstadisticas()
    {
        $sql = "
        SELECT 
            COUNT(*) AS total_registrados,
            SUM(CASE WHEN estado = 'Completado' THEN 1 ELSE 0 END) AS completados,
            SUM(CASE WHEN estado = 'Vencido' THEN 1 ELSE 0 END) AS vencidos,
            SUM(CASE WHEN estado NOT IN ('Completado', 'Vencido') THEN 1 ELSE 0 END) AS en_curso -- CORREGIDO: Cuenta todo lo que no esté completado o vencido
        FROM proyectos
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Asegurar que todos los valores sean numéricos (evita NULLs si la tabla está vacía)
        $stats['total_registrados'] = (int)($stats['total_registrados'] ?? 0);
        $stats['completados'] = (int)($stats['completados'] ?? 0);
        $stats['vencidos'] = (int)($stats['vencidos'] ?? 0);
        $stats['en_curso'] = (int)($stats['en_curso'] ?? 0);

        return $stats;
    }
}
