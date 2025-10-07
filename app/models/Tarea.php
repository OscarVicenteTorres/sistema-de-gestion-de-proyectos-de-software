<?php
require_once __DIR__ . "/../config/database.php";

/**
 * MODELO TAREA - BACKEND PARA ADMINISTRADOR
 * 
 * Maneja toda la lógica de tareas desde la perspectiva del administrador.
 * Incluye CRUD completo, validaciones y métodos específicos para gestión administrativa.
 * Cada método retorna arrays asociativos que pueden ser convertidos fácilmente a JSON.
 */
class Tarea {
    private $conn;
    
    // Constantes para las áreas permitidas (solo 3 como especificó el cliente)
    const AREAS_PERMITIDAS = ['Frontend', 'Backend', 'Infraestructura'];
    const ESTADOS_PERMITIDOS = ['Pendiente', 'En Progreso', 'Completado', 'Pausado', 'Cancelado'];

    public function __construct() {
        $this->conn = Database::connect();
    }

    /**
     * Obtiene todas las tareas para el dashboard del administrador
     * 
     * @param array $filtros - Filtros opcionales: ['proyecto_id', 'area', 'estado', 'usuario_id']
     * @return array - Lista de tareas con información completa
     */
    public function obtenerTodas($filtros = []) {
        $sql = "SELECT 
                    t.id_tarea,
                    t.titulo,
                    t.descripcion,
                    t.area_asignada,
                    t.fecha_inicio,
                    t.fecha_limite,
                    t.fecha_creacion,
                    t.estado,
                    t.porcentaje_avance,
                    
                    -- Información del proyecto
                    p.nombre as proyecto_nombre,
                    p.area as proyecto_area,
                    
                    -- Información del usuario asignado
                    u.nombre as usuario_nombre,
                    u.apellido as usuario_apellido,
                    u.area_trabajo as usuario_area,
                    
                    -- Calcular días restantes
                    DATEDIFF(t.fecha_limite, CURDATE()) as dias_restantes,
                    
                    -- Estado del progreso
                    CASE 
                        WHEN t.fecha_limite < CURDATE() AND t.estado != 'Completado' THEN 'Vencido'
                        WHEN DATEDIFF(t.fecha_limite, CURDATE()) <= 3 AND t.estado != 'Completado' THEN 'Urgente'
                        ELSE 'Normal'
                    END as prioridad
                    
                FROM tareas t
                INNER JOIN proyectos p ON t.id_proyecto = p.id_proyecto
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario";
        
        // Aplicar filtros dinámicamente
        $condiciones = [];
        $parametros = [];
        
        if (!empty($filtros['proyecto_id'])) {
            $condiciones[] = "t.id_proyecto = :proyecto_id";
            $parametros[':proyecto_id'] = $filtros['proyecto_id'];
        }
        
        if (!empty($filtros['area'])) {
            $condiciones[] = "t.area_asignada = :area";
            $parametros[':area'] = $filtros['area'];
        }
        
        if (!empty($filtros['estado'])) {
            $condiciones[] = "t.estado = :estado";
            $parametros[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['usuario_id'])) {
            $condiciones[] = "t.id_usuario = :usuario_id";
            $parametros[':usuario_id'] = $filtros['usuario_id'];
        }
        
        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY t.fecha_limite ASC, t.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($parametros);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una tarea específica por ID
     * 
     * @param int $id - ID de la tarea
     * @return array|false - Datos de la tarea o false si no existe
     */
    public function obtenerPorId($id) {
        $sql = "SELECT 
                    t.*,
                    p.nombre as proyecto_nombre,
                    u.nombre as usuario_nombre,
                    u.apellido as usuario_apellido
                FROM tareas t
                INNER JOIN proyectos p ON t.id_proyecto = p.id_proyecto
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE t.id_tarea = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva tarea
     * Campos requeridos: titulo, id_proyecto, id_usuario, area_asignada
     * 
     * @param array $datos - Datos de la tarea
     * @return array - ['exito' => bool, 'mensaje' => string, 'id' => int|null]
     */
    public function crear($datos) {
        // Validaciones básicas
        $validacion = $this->validarDatos($datos);
        if (!$validacion['exito']) {
            return $validacion;
        }
        
        try {
            $sql = "INSERT INTO tareas (
                        id_proyecto,
                        id_usuario,
                        titulo,
                        descripcion,
                        area_asignada,
                        fecha_inicio,
                        fecha_limite,
                        estado,
                        porcentaje_avance
                    ) VALUES (
                        :id_proyecto,
                        :id_usuario,
                        :titulo,
                        :descripcion,
                        :area_asignada,
                        :fecha_inicio,
                        :fecha_limite,
                        :estado,
                        :porcentaje_avance
                    )";
            
            $stmt = $this->conn->prepare($sql);
            
            // Preparar parámetros con valores por defecto
            $params = [
                ':id_proyecto' => $datos['id_proyecto'],
                ':id_usuario' => $datos['id_usuario'],
                ':titulo' => $datos['titulo'],
                ':descripcion' => $datos['descripcion'] ?? '',
                ':area_asignada' => $datos['area_asignada'],
                ':fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                ':fecha_limite' => $datos['fecha_limite'] ?? null,
                ':estado' => $datos['estado'] ?? 'Pendiente',
                ':porcentaje_avance' => $datos['porcentaje_avance'] ?? 0
            ];
            
            $resultado = $stmt->execute($params);
            
            if ($resultado) {
                $id_tarea = $this->conn->lastInsertId();
                return [
                    'exito' => true,
                    'mensaje' => 'Tarea creada exitosamente',
                    'id' => $id_tarea
                ];
            } else {
                return [
                    'exito' => false,
                    'mensaje' => 'Error al crear la tarea'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error al crear tarea: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Actualiza una tarea existente
     * 
     * @param int $id - ID de la tarea
     * @param array $datos - Nuevos datos
     * @return array - ['exito' => bool, 'mensaje' => string]
     */
    public function actualizar($id, $datos) {
        // Verificar que la tarea existe
        if (!$this->obtenerPorId($id)) {
            return ['exito' => false, 'mensaje' => 'La tarea no existe'];
        }
        
        // Validar datos
        $validacion = $this->validarDatos($datos, false); // false = no es creación
        if (!$validacion['exito']) {
            return $validacion;
        }
        
        try {
            $sql = "UPDATE tareas SET 
                        titulo = :titulo,
                        descripcion = :descripcion,
                        area_asignada = :area_asignada,
                        fecha_inicio = :fecha_inicio,
                        fecha_limite = :fecha_limite,
                        estado = :estado,
                        porcentaje_avance = :porcentaje_avance
                    WHERE id_tarea = :id";
            
            $stmt = $this->conn->prepare($sql);
            
            $params = [
                ':titulo' => $datos['titulo'],
                ':descripcion' => $datos['descripcion'] ?? '',
                ':area_asignada' => $datos['area_asignada'],
                ':fecha_inicio' => $datos['fecha_inicio'],
                ':fecha_limite' => $datos['fecha_limite'],
                ':estado' => $datos['estado'],
                ':porcentaje_avance' => $datos['porcentaje_avance'] ?? 0,
                ':id' => $id
            ];
            
            $resultado = $stmt->execute($params);
            
            return [
                'exito' => $resultado,
                'mensaje' => $resultado ? 'Tarea actualizada exitosamente' : 'Error al actualizar la tarea'
            ];
            
        } catch (PDOException $e) {
            error_log("Error al actualizar tarea: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Elimina una tarea (solo administrador)
     * 
     * @param int $id - ID de la tarea
     * @return array - ['exito' => bool, 'mensaje' => string]
     */
    public function eliminar($id) {
        // Verificar que la tarea existe
        $tarea = $this->obtenerPorId($id);
        if (!$tarea) {
            return ['exito' => false, 'mensaje' => 'La tarea no existe'];
        }
        
        try {
            // Eliminar justificaciones asociadas primero
            $sqlJustificaciones = "DELETE FROM justificaciones_tareas WHERE id_tarea = :id";
            $stmtJust = $this->conn->prepare($sqlJustificaciones);
            $stmtJust->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtJust->execute();
            
            // Eliminar la tarea
            $sql = "DELETE FROM tareas WHERE id_tarea = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $resultado = $stmt->execute();
            
            return [
                'exito' => $resultado,
                'mensaje' => $resultado ? 'Tarea eliminada exitosamente' : 'Error al eliminar la tarea'
            ];
            
        } catch (PDOException $e) {
            error_log("Error al eliminar tarea: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Obtiene estadísticas para dashboard del administrador
     * 
     * @return array - Estadísticas completas
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_tareas,
                        
                        -- Por estado
                        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'En Progreso' THEN 1 ELSE 0 END) as en_progreso,
                        SUM(CASE WHEN estado = 'Completado' THEN 1 ELSE 0 END) as completadas,
                        SUM(CASE WHEN estado = 'Pausado' THEN 1 ELSE 0 END) as pausadas,
                        
                        -- Por área
                        SUM(CASE WHEN area_asignada = 'Frontend' THEN 1 ELSE 0 END) as frontend,
                        SUM(CASE WHEN area_asignada = 'Backend' THEN 1 ELSE 0 END) as backend,
                        SUM(CASE WHEN area_asignada = 'Infraestructura' THEN 1 ELSE 0 END) as infraestructura,
                        
                        -- Urgentes y vencidas
                        SUM(CASE WHEN fecha_limite < CURDATE() AND estado != 'Completado' THEN 1 ELSE 0 END) as vencidas,
                        SUM(CASE WHEN DATEDIFF(fecha_limite, CURDATE()) <= 3 AND estado != 'Completado' THEN 1 ELSE 0 END) as urgentes,
                        
                        -- Progreso promedio
                        AVG(porcentaje_avance) as progreso_promedio
                        
                    FROM tareas";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calcular porcentajes
            $total = $stats['total_tareas'];
            if ($total > 0) {
                $stats['porcentaje_completadas'] = round(($stats['completadas'] / $total) * 100, 2);
                $stats['porcentaje_en_progreso'] = round(($stats['en_progreso'] / $total) * 100, 2);
                $stats['porcentaje_pendientes'] = round(($stats['pendientes'] / $total) * 100, 2);
            } else {
                $stats['porcentaje_completadas'] = 0;
                $stats['porcentaje_en_progreso'] = 0;
                $stats['porcentaje_pendientes'] = 0;
            }
            
            $stats['progreso_promedio'] = round($stats['progreso_promedio'], 2);
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Valida los datos de una tarea antes de guardar
     * 
     * @param array $datos - Datos a validar
     * @param bool $esCreacion - true si es creación, false si es actualización
     * @return array - ['exito' => bool, 'mensaje' => string]
     */
    private function validarDatos($datos, $esCreacion = true) {
        // Título requerido
        if (empty($datos['titulo']) || strlen(trim($datos['titulo'])) < 3) {
            return ['exito' => false, 'mensaje' => 'El título es requerido y debe tener al menos 3 caracteres'];
        }
        
        // Área válida
        if (empty($datos['area_asignada']) || !in_array($datos['area_asignada'], self::AREAS_PERMITIDAS)) {
            return ['exito' => false, 'mensaje' => 'El área asignada debe ser: Frontend, Backend o Infraestructura'];
        }
        
        // Estado válido
        if (!empty($datos['estado']) && !in_array($datos['estado'], self::ESTADOS_PERMITIDOS)) {
            return ['exito' => false, 'mensaje' => 'Estado no válido'];
        }
        
        // Porcentaje válido
        if (isset($datos['porcentaje_avance'])) {
            $porcentaje = (int)$datos['porcentaje_avance'];
            if ($porcentaje < 0 || $porcentaje > 100) {
                return ['exito' => false, 'mensaje' => 'El porcentaje debe estar entre 0 y 100'];
            }
        }
        
        // Fechas válidas
        if (!empty($datos['fecha_limite'])) {
            if (!$this->validarFecha($datos['fecha_limite'])) {
                return ['exito' => false, 'mensaje' => 'Formato de fecha límite inválido'];
            }
        }
        
        if (!empty($datos['fecha_inicio'])) {
            if (!$this->validarFecha($datos['fecha_inicio'])) {
                return ['exito' => false, 'mensaje' => 'Formato de fecha de inicio inválido'];
            }
        }
        
        return ['exito' => true, 'mensaje' => 'Datos válidos'];
    }
    
    /**
     * Validar formato de fecha
     */
    private function validarFecha($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    /**
     * Obtiene las opciones disponibles para formularios
     * 
     * @return array - Arrays con opciones para selects
     */
    public function obtenerOpcionesFormulario() {
        return [
            'areas' => self::AREAS_PERMITIDAS,
            'estados' => self::ESTADOS_PERMITIDOS
        ];
    }
}
