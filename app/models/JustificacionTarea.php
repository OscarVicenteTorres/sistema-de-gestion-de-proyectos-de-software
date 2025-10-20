<?php
require_once __DIR__ . "/../config/database.php";

/**
 * MODELO JUSTIFICACION TAREA - BACKEND PARA ADMINISTRADOR
 * 
 * Maneja las solicitudes de extensión de tiempo para tareas.
 * El desarrollador crea justificaciones y el administrador las aprueba/rechaza.
 * Cada método retorna arrays asociativos que pueden ser convertidos fácilmente a JSON.
 */
class JustificacionTarea {
    private $conn;
    
    // Estados permitidos para las justificaciones
    const ESTADOS_PERMITIDOS = ['Pendiente', 'Aprobada', 'Rechazada'];

    public function __construct() {
        $this->conn = Database::connect();
    }

    /**
     * Obtiene todas las justificaciones para el dashboard del administrador
     * 
     * @param array $filtros - Filtros opcionales: ['estado', 'fecha_desde', 'fecha_hasta', 'tarea_id']
     * @return array - Lista de justificaciones con información completa
     */
    public function obtenerTodas($filtros = []) {
        $sql = "SELECT 
                    jt.id_justificacion_tarea,
                    jt.motivo,
                    jt.nueva_fecha_limite,
                    jt.estado,
                    jt.fecha_solicitud,
                    jt.fecha_respuesta,
                    jt.comentarios_admin,
                    
                    -- Información de la tarea
                    t.titulo as tarea_titulo,
                    t.descripcion as tarea_descripcion,
                    t.fecha_limite as fecha_limite_original,
                    t.area_asignada as tarea_area,
                    t.porcentaje_avance,
                    
                    -- Información del proyecto
                    p.nombre as proyecto_nombre,
                    
                    -- Información del desarrollador (quien solicita)
                    u_dev.nombre as desarrollador_nombre,
                    u_dev.apellido as desarrollador_apellido,
                    u_dev.area_trabajo as desarrollador_area,
                    
                    -- Información del administrador (quien responde)
                    u_admin.nombre as admin_nombre,
                    u_admin.apellido as admin_apellido,
                    
                    -- Calcular días de extensión solicitados
                    DATEDIFF(jt.nueva_fecha_limite, t.fecha_limite) as dias_extension,
                    
                    -- Urgencia de la solicitud
                    CASE 
                        WHEN jt.estado = 'Pendiente' AND DATEDIFF(CURDATE(), jt.fecha_solicitud) > 3 THEN 'Urgente'
                        WHEN jt.estado = 'Pendiente' THEN 'Normal'
                        ELSE 'Procesada'
                    END as urgencia
                    
                FROM justificaciones_tareas jt
                INNER JOIN tareas t ON jt.id_tarea = t.id_tarea
                INNER JOIN proyectos p ON t.id_proyecto = p.id_proyecto
                INNER JOIN usuarios u_dev ON t.id_usuario = u_dev.id_usuario
                LEFT JOIN usuarios u_admin ON jt.respondido_por = u_admin.id_usuario";
        
        // Aplicar filtros dinámicamente
        $condiciones = [];
        $parametros = [];
        
        if (!empty($filtros['estado'])) {
            $condiciones[] = "jt.estado = :estado";
            $parametros[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $condiciones[] = "DATE(jt.fecha_solicitud) >= :fecha_desde";
            $parametros[':fecha_desde'] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $condiciones[] = "DATE(jt.fecha_solicitud) <= :fecha_hasta";
            $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        
        if (!empty($filtros['tarea_id'])) {
            $condiciones[] = "jt.id_tarea = :tarea_id";
            $parametros[':tarea_id'] = $filtros['tarea_id'];
        }
        
        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY 
                    CASE jt.estado 
                        WHEN 'Pendiente' THEN 1 
                        WHEN 'Aprobada' THEN 2 
                        WHEN 'Rechazada' THEN 3 
                    END,
                    jt.fecha_solicitud DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($parametros);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una justificación específica por ID
     * 
     * @param int $id - ID de la justificación
     * @return array|false - Datos de la justificación o false si no existe
     */
    public function obtenerPorId($id) {
        $sql = "SELECT 
                    jt.*,
                    t.titulo as tarea_titulo,
                    t.fecha_limite as fecha_limite_original,
                    p.nombre as proyecto_nombre,
                    u_dev.nombre as desarrollador_nombre,
                    u_dev.apellido as desarrollador_apellido,
                    u_admin.nombre as admin_nombre,
                    u_admin.apellido as admin_apellido
                FROM justificaciones_tareas jt
                INNER JOIN tareas t ON jt.id_tarea = t.id_tarea
                INNER JOIN proyectos p ON t.id_proyecto = p.id_proyecto
                INNER JOIN usuarios u_dev ON t.id_usuario = u_dev.id_usuario
                LEFT JOIN usuarios u_admin ON jt.respondido_por = u_admin.id_usuario
                WHERE jt.id_justificacion_tarea = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Aprueba una justificación (solo administrador)
     * Actualiza automáticamente la fecha límite de la tarea
     * 
     * @param int $id - ID de la justificación
     * @param int $admin_id - ID del administrador que aprueba
     * @param string $comentarios - Comentarios adicionales del admin
     * @return array - ['exito' => bool, 'mensaje' => string]
     */
    public function aprobar($id, $admin_id, $comentarios = '') {
        // Verificar que la justificación existe y está pendiente
        $justificacion = $this->obtenerPorId($id);
        if (!$justificacion) {
            return ['exito' => false, 'mensaje' => 'La justificación no existe'];
        }
        
        if ($justificacion['estado'] !== 'Pendiente') {
            return ['exito' => false, 'mensaje' => 'La justificación ya fue procesada'];
        }
        
        try {
            // Iniciar transacción para garantizar consistencia
            $this->conn->beginTransaction();
            
            // 1. Actualizar la justificación
            $sqlJust = "UPDATE justificaciones_tareas 
                       SET estado = 'Aprobada',
                           fecha_respuesta = CURRENT_TIMESTAMP,
                           respondido_por = :admin_id,
                           comentarios_admin = :comentarios
                       WHERE id_justificacion_tarea = :id";
            
            $stmtJust = $this->conn->prepare($sqlJust);
            $stmtJust->execute([
                ':admin_id' => $admin_id,
                ':comentarios' => $comentarios,
                ':id' => $id
            ]);
            
            // 2. Actualizar la fecha límite de la tarea
            $sqlTarea = "UPDATE tareas 
                        SET fecha_limite = :nueva_fecha
                        WHERE id_tarea = :tarea_id";
            
            $stmtTarea = $this->conn->prepare($sqlTarea);
            $stmtTarea->execute([
                ':nueva_fecha' => $justificacion['nueva_fecha_limite'],
                ':tarea_id' => $justificacion['id_tarea']
            ]);
            
            // Confirmar transacción
            $this->conn->commit();
            
            return [
                'exito' => true,
                'mensaje' => 'Justificación aprobada y fecha de tarea actualizada'
            ];
            
        } catch (PDOException $e) {
            // Revertir cambios en caso de error
            $this->conn->rollback();
            error_log("Error al aprobar justificación: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Rechaza una justificación (solo administrador)
     * 
     * @param int $id - ID de la justificación
     * @param int $admin_id - ID del administrador que rechaza
     * @param string $comentarios - Razón del rechazo (requerido)
     * @return array - ['exito' => bool, 'mensaje' => string]
     */
    public function rechazar($id, $admin_id, $comentarios) {
        // Validar que se proporcionen comentarios para el rechazo
        if (empty(trim($comentarios))) {
            return ['exito' => false, 'mensaje' => 'Debe proporcionar una razón para el rechazo'];
        }
        
        // Verificar que la justificación existe y está pendiente
        $justificacion = $this->obtenerPorId($id);
        if (!$justificacion) {
            return ['exito' => false, 'mensaje' => 'La justificación no existe'];
        }
        
        if ($justificacion['estado'] !== 'Pendiente') {
            return ['exito' => false, 'mensaje' => 'La justificación ya fue procesada'];
        }
        
        try {
            $sql = "UPDATE justificaciones_tareas 
                   SET estado = 'Rechazada',
                       fecha_respuesta = CURRENT_TIMESTAMP,
                       respondido_por = :admin_id,
                       comentarios_admin = :comentarios
                   WHERE id_justificacion_tarea = :id";
            
            $stmt = $this->conn->prepare($sql);
            $resultado = $stmt->execute([
                ':admin_id' => $admin_id,
                ':comentarios' => $comentarios,
                ':id' => $id
            ]);
            
            return [
                'exito' => $resultado,
                'mensaje' => $resultado ? 'Justificación rechazada' : 'Error al rechazar la justificación'
            ];
            
        } catch (PDOException $e) {
            error_log("Error al rechazar justificación: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Crea una nueva justificación (para desarrolladores)
     * Método incluido para completitud del modelo
     * 
     * @param array $datos - Datos de la justificación
     * @return array - ['exito' => bool, 'mensaje' => string, 'id' => int|null]
     */
    public function crear($datos) {
        // Validaciones básicas
        if (empty($datos['id_tarea'])) {
            return ['exito' => false, 'mensaje' => 'ID de tarea requerido'];
        }
        
        if (empty(trim($datos['motivo']))) {
            return ['exito' => false, 'mensaje' => 'El motivo es requerido'];
        }
        
        if (empty($datos['nueva_fecha_limite'])) {
            return ['exito' => false, 'mensaje' => 'Nueva fecha límite requerida'];
        }
        
        // Verificar que la nueva fecha sea posterior a la actual
        if (strtotime($datos['nueva_fecha_limite']) <= strtotime(date('Y-m-d'))) {
            return ['exito' => false, 'mensaje' => 'La nueva fecha debe ser posterior a hoy'];
        }
        
        try {
            $sql = "INSERT INTO justificaciones_tareas (id_tarea, motivo, nueva_fecha_limite)
                   VALUES (:id_tarea, :motivo, :nueva_fecha_limite)";
            
            $stmt = $this->conn->prepare($sql);
            $resultado = $stmt->execute([
                ':id_tarea' => $datos['id_tarea'],
                ':motivo' => trim($datos['motivo']),
                ':nueva_fecha_limite' => $datos['nueva_fecha_limite']
            ]);
            
            if ($resultado) {
                return [
                    'exito' => true,
                    'mensaje' => 'Justificación creada exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return ['exito' => false, 'mensaje' => 'Error al crear la justificación'];
            }
            
        } catch (PDOException $e) {
            error_log("Error al crear justificación: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Obtiene estadísticas de justificaciones para dashboard del administrador
     * 
     * @return array - Estadísticas completas
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_justificaciones,
                        
                        -- Por estado
                        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'Aprobada' THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN estado = 'Rechazada' THEN 1 ELSE 0 END) as rechazadas,
                        
                        -- Tiempo de respuesta promedio (solo procesadas)
                        AVG(CASE 
                            WHEN fecha_respuesta IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, fecha_solicitud, fecha_respuesta) 
                        END) as tiempo_respuesta_horas,
                        
                        -- Solicitudes urgentes (más de 3 días pendientes)
                        SUM(CASE 
                            WHEN estado = 'Pendiente' AND DATEDIFF(CURDATE(), fecha_solicitud) > 3 
                            THEN 1 ELSE 0 
                        END) as urgentes,
                        
                        -- Extensión promedio solicitada (en días)
                        AVG(DATEDIFF(nueva_fecha_limite, 
                            (SELECT fecha_limite FROM tareas WHERE tareas.id_tarea = justificaciones_tareas.id_tarea)
                        )) as extension_promedio_dias
                        
                    FROM justificaciones_tareas";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calcular porcentajes
            $total = $stats['total_justificaciones'];
            if ($total > 0) {
                $stats['porcentaje_aprobadas'] = round(($stats['aprobadas'] / $total) * 100, 2);
                $stats['porcentaje_rechazadas'] = round(($stats['rechazadas'] / $total) * 100, 2);
                $stats['porcentaje_pendientes'] = round(($stats['pendientes'] / $total) * 100, 2);
            } else {
                $stats['porcentaje_aprobadas'] = 0;
                $stats['porcentaje_rechazadas'] = 0;
                $stats['porcentaje_pendientes'] = 0;
            }
            
            // Redondear promedios
            $stats['tiempo_respuesta_horas'] = round($stats['tiempo_respuesta_horas'], 2);
            $stats['extension_promedio_dias'] = round($stats['extension_promedio_dias'], 2);
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de justificaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las opciones disponibles para formularios
     * 
     * @return array - Arrays con opciones para selects
     */
    public function obtenerOpcionesFormulario() {
        return [
            'estados' => self::ESTADOS_PERMITIDOS
        ];
    }
}