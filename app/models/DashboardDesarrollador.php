<?php
require_once __DIR__ . "/../config/database.php";

/**
 * MODELO: DashboardDesarrollador
 * 
 * Maneja todas las operaciones del dashboard del desarrollador
 * - Obtener proyectos asignados
 * - Obtener tareas por proyecto
 * - Obtener historial de avances
 * - Guardar avances de tareas
 */
class DashboardDesarrollador
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Obtener todos los proyectos asignados a un desarrollador
     * 
     * @param int $id_usuario - ID del usuario (desarrollador)
     * @return array - Array con todos los proyectos asignados
     */
    public function obtenerProyectos($id_usuario)
    {
        try {
            $sql = "SELECT DISTINCT
                        p.id_proyecto,
                        p.nombre,
                        p.descripcion,
                        p.fecha_inicio,
                        p.fecha_fin,
                        p.estado,
                        p.area AS categoria,
                        p.porcentaje_avance
                    FROM proyectos p
                    INNER JOIN tareas t ON p.id_proyecto = t.id_proyecto
                    WHERE t.id_usuario = :id_usuario
                    ORDER BY p.fecha_inicio DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener proyectos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las tareas de un proyecto específico
     * 
     * @param int $id_proyecto - ID del proyecto
     * @return array - Array con todas las tareas del proyecto
     */
    public function obtenerTareasPorProyecto($id_proyecto)
    {
        try {
            $sql = "SELECT 
                        t.id_tarea,
                        t.id_usuario,
                        t.titulo,
                        t.descripcion,
                        t.area_asignada,
                        t.fecha_inicio,
                        t.fecha_limite,
                        t.estado,
                        t.porcentaje_avance,
                        t.fecha_creacion,
                        u.nombre as usuario_nombre,
                        u.apellido as usuario_apellido
                    FROM tareas t
                    LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
                    WHERE t.id_proyecto = :id_proyecto
                    ORDER BY 
                        CASE 
                            WHEN t.estado = 'Completado' THEN 2
                            ELSE 1
                        END ASC,
                        t.fecha_limite ASC,
                        t.fecha_creacion DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_proyecto', $id_proyecto, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener tareas por proyecto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener el historial de notas/avances de una tarea
     * 
     * @param int $id_tarea - ID de la tarea
     * @return array - Array con todas las notas de la tarea
     */
    public function obtenerNotasTarea($id_tarea)
    {
        try {
            $sql = "SELECT 
                        id_nota,
                        id_tarea,
                        porcentaje_anterior,
                        porcentaje_nuevo,
                        nota_desarrollador,
                        fecha_envio
                    FROM notas_tareas
                    WHERE id_tarea = :id_tarea
                    ORDER BY fecha_envio DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_tarea', $id_tarea, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener notas de tarea: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guardar una nota/avance de tarea
     * 
     * @param array $datos - Datos de la nota
     * @return array - ['exito' => bool, 'mensaje' => string, 'id' => int|null]
     */
    public function guardarAvance($datos)
    {
        try {
            // Primero obtener el porcentaje anterior
            $sqlActual = "SELECT porcentaje_avance FROM tareas WHERE id_tarea = :id_tarea";
            $stmtActual = $this->conn->prepare($sqlActual);
            $stmtActual->bindParam(':id_tarea', $datos['id_tarea'], PDO::PARAM_INT);
            $stmtActual->execute();
            $tareaActual = $stmtActual->fetch(PDO::FETCH_ASSOC);
            $porcentaje_anterior = $tareaActual['porcentaje_avance'] ?? 0;

            // Guardar nota
            $sqlNota = "INSERT INTO notas_tareas (
                            id_tarea,
                            porcentaje_anterior,
                            porcentaje_nuevo,
                            nota_desarrollador
                        ) VALUES (
                            :id_tarea,
                            :porcentaje_anterior,
                            :porcentaje_nuevo,
                            :nota_desarrollador
                        )";

            $stmtNota = $this->conn->prepare($sqlNota);
            $stmtNota->execute([
                ':id_tarea' => $datos['id_tarea'],
                ':porcentaje_anterior' => $porcentaje_anterior,
                ':porcentaje_nuevo' => $datos['porcentaje_nuevo'] ?? 0,
                ':nota_desarrollador' => $datos['nota_desarrollador'] ?? ''
            ]);

            // Actualizar tarea con nuevo porcentaje
            $sqlUpdate = "UPDATE tareas 
                         SET porcentaje_avance = :porcentaje_nuevo,
                             estado = CASE 
                                WHEN :porcentaje_nuevo >= 100 THEN 'Completado'
                                WHEN :porcentaje_nuevo > 0 THEN 'En Progreso'
                                ELSE 'Pendiente'
                             END
                         WHERE id_tarea = :id_tarea";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':porcentaje_nuevo' => $datos['porcentaje_nuevo'] ?? 0,
                ':id_tarea' => $datos['id_tarea']
            ]);

            // Actualizar porcentaje del proyecto
            $this->recalcularProyecto($datos['id_proyecto'] ?? $this->obtenerIdProyecto($datos['id_tarea']));

            return [
                'exito' => true,
                'mensaje' => 'Avance guardado exitosamente',
                'id' => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Error al guardar avance: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar el avance'
            ];
        }
    }

    /**
     * Obtener tareas completadas (100%)
     * 
     * @param int $id_usuario - ID del usuario
     * @return array - Array con tareas completadas
     */
    public function obtenerTareasCompletadas($id_usuario)
    {
        try {
            $sql = "SELECT 
                        t.id_tarea,
                        t.titulo,
                        t.descripcion,
                        t.area_asignada,
                        t.fecha_limite,
                        t.porcentaje_avance,
                        p.nombre as proyecto_nombre,
                        p.id_proyecto,
                        COUNT(nt.id_nota) as total_notas
                    FROM tareas t
                    INNER JOIN proyectos p ON t.id_proyecto = p.id_proyecto
                    LEFT JOIN notas_tareas nt ON t.id_tarea = nt.id_tarea
                    WHERE t.id_usuario = :id_usuario 
                    AND t.porcentaje_avance >= 100
                    GROUP BY t.id_tarea
                    ORDER BY t.fecha_limite DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener tareas completadas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener ID del proyecto de una tarea
     * 
     * @param int $id_tarea - ID de la tarea
     * @return int - ID del proyecto
     */
    private function obtenerIdProyecto($id_tarea)
    {
        try {
            $sql = "SELECT id_proyecto FROM tareas WHERE id_tarea = :id_tarea";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_tarea', $id_tarea, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['id_proyecto'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Recalcular porcentaje del proyecto
     * 
     * @param int $id_proyecto - ID del proyecto
     * @return bool - true si se actualizó correctamente
     */
    private function recalcularProyecto($id_proyecto)
    {
        try {
            $sql = "SELECT AVG(porcentaje_avance) AS promedio FROM tareas WHERE id_proyecto = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id_proyecto]);
            $promedio = $stmt->fetchColumn();

            if ($promedio === null) $promedio = 0;

            $nuevoEstado = 'Pendiente';
            if ($promedio > 0 && $promedio < 100) {
                $nuevoEstado = 'Activo';
            } elseif ($promedio >= 100) {
                $nuevoEstado = 'Completado';
            }

            $update = "UPDATE proyectos 
                       SET porcentaje_avance = :promedio, estado = :estado 
                       WHERE id_proyecto = :id";
            $stmt2 = $this->conn->prepare($update);
            $stmt2->execute([
                ':promedio' => round($promedio, 2),
                ':estado' => $nuevoEstado,
                ':id' => $id_proyecto
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Error al recalcular proyecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guardar justificación de extensión de tarea
     * 
     * @param array $datos - Datos de la justificación
     * @return array - ['exito' => bool, 'mensaje' => string]
     */
    public function guardarJustificacion($datos)
    {
        try {
            $sql = "INSERT INTO justificaciones_tareas (
                        id_tarea,
                        motivo,
                        nueva_fecha_limite,
                        estado,
                        fecha_solicitud
                    ) VALUES (
                        :id_tarea,
                        :motivo,
                        :nueva_fecha_limite,
                        'Pendiente',
                        NOW()
                    )";

            $stmt = $this->conn->prepare($sql);
            $resultado = $stmt->execute([
                ':id_tarea' => $datos['id_tarea'],
                ':motivo' => $datos['motivo'] ?? '',
                ':nueva_fecha_limite' => $datos['nueva_fecha_limite']
            ]);

            if ($resultado) {
                // Crear notificación para el admin
                // Buscar ID del admin dinámicamente
                $sqlAdmin = "SELECT id_usuario FROM usuarios WHERE id_rol = 1 LIMIT 1";
                $stmtAdmin = $this->conn->prepare($sqlAdmin);
                $stmtAdmin->execute();
                $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
                
                if ($admin) {
                    $this->crearNotificacion(
                        $admin['id_usuario'],
                        "Nueva solicitud de extensión de tarea",
                        'Tarea'
                    );
                }

                return [
                    'exito' => true,
                    'mensaje' => 'Justificación guardada exitosamente'
                ];
            }

            return [
                'exito' => false,
                'mensaje' => 'Error al guardar la justificación'
            ];
        } catch (PDOException $e) {
            error_log("Error al guardar justificación: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Crear notificación
     * 
     * @param int $id_usuario - ID del usuario que recibirá la notificación
     * @param string $mensaje - Mensaje de la notificación
     * @param string $tipo - Tipo de notificación (Tarea, Proyecto, Asistencia)
     * @return bool
     */
    private function crearNotificacion($id_usuario, $mensaje, $tipo)
    {
        try {
            $sql = "INSERT INTO notificaciones (id_usuario, mensaje, tipo, leida, fecha)
                    VALUES (:id_usuario, :mensaje, :tipo, 0, NOW())";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':mensaje' => $mensaje,
                ':tipo' => $tipo
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear notificación: " . $e->getMessage());
            return false;
        }
    }
}
