<?php
require_once __DIR__ . "/../models/Tarea.php";
require_once __DIR__ . "/../models/JustificacionTarea.php";
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../../core/BaseApiController.php';

//CONTROLADOR TAREA OPTIMIZADO - BACKEND PARA ADMINISTRADOR 
//Refactorizado con POO, elimina duplicación de código y mejora mantenibilidad.
class TareaController extends BaseApiController {
    private Tarea $tareaModel;
    private JustificacionTarea $justificacionModel;
    private Usuario $usuarioModel;

    public function __construct() {
        $this->tareaModel = new Tarea();
        $this->justificacionModel = new JustificacionTarea();
        $this->usuarioModel = new Usuario();
        $this->verificarAdmin();
    }

    // Vista principal de tareas
    public function index(): void {
        require __DIR__ . "/../views/admin/tareas.php";
    }

    // Lista todas las tareas con filtros
    // GET /index.php?c=Tarea&a=listar
    public function listar(): void {
        $this->ejecutarOperacion(function() {
            // Agregar más filtros posibles desde la petición para que la UI pueda pedir listados filtrados
            $filtros = $this->obtenerFiltros([
                'proyecto_id', 'area', 'estado', 'usuario_id',
                'titulo_like', 'descripcion_like',
                'fecha_creacion_from', 'fecha_creacion_to',
                'fecha_limite_from', 'fecha_limite_to'
            ]);

            $tareas = $this->tareaModel->obtenerTodas($filtros);
            
            return [
                'tareas' => $tareas,
                'total' => count($tareas),
                'filtros_aplicados' => $filtros
            ];
        });
    }

    // Obtiene estadísticas de tareas
    // GET /index.php?c=Tarea&a=estadisticas
    public function estadisticas(): void {
        $this->ejecutarOperacion(function() {
            // Aceptar proyecto_id opcional para escopar las estadísticas al proyecto seleccionado
            $proyectoId = isset($_GET['proyecto_id']) && is_numeric($_GET['proyecto_id']) ? intval($_GET['proyecto_id']) : null;
            return $this->tareaModel->obtenerEstadisticas($proyectoId);
        });
    }

    // Obtiene opciones para formularios (usuarios, proyectos)
    // GET /index.php?c=Tarea&a=opcionesFormulario
    public function opcionesFormulario(): void {
        $this->ejecutarOperacion(function() {
            return $this->tareaModel->obtenerOpcionesFormulario();
        });
    }

    // Crea nueva tarea
    // POST /index.php?c=Tarea&a=guardar
    public function guardar(): void {
        $datos = $this->getInput();
        
        // Validar campos requeridos
        $this->validarCampos($datos, [
            'titulo', 'descripcion', 'id_usuario', 'area_asignada', 
            'fecha_limite', 'id_proyecto'
        ]);

        // Sanitizar datos
        $datosSanitizados = $this->sanitizarDatosTarea($datos);
        
        $this->ejecutarOperacion(function() use ($datosSanitizados) {
            return $this->tareaModel->crear($datosSanitizados);
        });
    }

    // Actualiza tarea existente
    // POST /index.php?c=Tarea&a=actualizar
    public function actualizar(): void {
        $datos = $this->getInput();
        
        // Validar ID de tarea
        $id = $datos['id_tarea'] ?? $datos['id'] ?? null;
        if (!$id) {
            $this->jsonError('ID de tarea requerido');
        }

        // Validar campos requeridos
        $this->validarCampos($datos, ['titulo', 'descripcion']);
        
        // Sanitizar datos
        $datosSanitizados = $this->sanitizarDatosTarea($datos);
        $datosSanitizados['id_tarea'] = $id;

        $this->ejecutarOperacion(function() use ($datosSanitizados, $id) {
            return $this->tareaModel->actualizar($id, $datosSanitizados);
        });
    }

    // Elimina tarea
    // POST /index.php?c=Tarea&a=eliminar
    public function eliminar(): void {
        $datos = $this->getInput();
        $id = $datos['id_tarea'] ?? $datos['id'] ?? null;
        
        if (!$id) {
            $this->jsonError('ID de tarea requerido');
        }

        $this->ejecutarOperacion(function() use ($id) {
            return $this->tareaModel->eliminar($id);
        });
    }

    // Obtiene justificaciones de una tarea
    // GET /index.php?c=Tarea&a=justificaciones&id_tarea=X
    public function justificaciones(): void {
        $idTarea = $_GET['id_tarea'] ?? null;
        
        if (!$idTarea) {
            $this->jsonError('ID de tarea requerido');
        }

        $this->ejecutarOperacion(function() use ($idTarea) {
            return $this->justificacionModel->obtenerTodas(['id_tarea' => $idTarea]);
        });
    }

    // Obtiene una tarea por ID
    // GET /index.php?c=Tarea&a=obtenerPorId&id=X
    public function obtenerPorId(): void {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->jsonError('ID de tarea requerido');
        }

        $this->ejecutarOperacion(function() use ($id) {
            return $this->tareaModel->obtenerPorId($id);
        });
    }

    // Actualiza porcentaje de avance de tarea
    // POST /index.php?c=Tarea&a=actualizarProgreso
    public function actualizarProgreso(): void {
        $datos = $this->getInput();
        
        $this->validarCampos($datos, ['id_tarea', 'porcentaje_avance']);
        
        $porcentaje = (int)$datos['porcentaje_avance'];
        if ($porcentaje < 0 || $porcentaje > 100) {
            $this->jsonError('Porcentaje debe estar entre 0 y 100');
        }

        $this->ejecutarOperacion(function() use ($datos, $porcentaje) {
            return $this->tareaModel->actualizar($datos['id_tarea'], [
                'porcentaje_avance' => $porcentaje,
                'estado' => $porcentaje == 100 ? 'Completado' : 'En Progreso'
            ]);
        });
    }

    // Sanitiza y valida datos de tarea
    private function sanitizarDatosTarea(array $datos): array {
        $sanitizado = [
            'titulo' => $this->sanitizar($datos['titulo']),
            'descripcion' => $this->sanitizar($datos['descripcion']),
            'area_asignada' => $this->sanitizar($datos['area_asignada']),
            'estado' => $datos['estado'] ?? 'Pendiente',
            'porcentaje_avance' => (int)($datos['porcentaje_avance'] ?? 0)
        ];

        // Validar y asignar fechas
        if (!empty($datos['fecha_inicio']) && $this->validarFecha($datos['fecha_inicio'])) {
            $sanitizado['fecha_inicio'] = $datos['fecha_inicio'];
        }
        
        if (!empty($datos['fecha_limite']) && $this->validarFecha($datos['fecha_limite'])) {
            $sanitizado['fecha_limite'] = $datos['fecha_limite'];
        }

        // Validar IDs numéricos
        foreach (['id_usuario', 'id_proyecto'] as $campo) {
            if (isset($datos[$campo]) && is_numeric($datos[$campo])) {
                $sanitizado[$campo] = (int)$datos[$campo];
            }
        }

        // Validar área usando la constante del modelo
        if (!in_array($sanitizado['area_asignada'], Tarea::AREAS_PERMITIDAS)) {
            $this->jsonError('Área asignada no válida');
        }

        return $sanitizado;
    }

    /**
     * NUEVA FUNCIÓN: Guardar avance de una tarea desde el dashboard del desarrollador
     * Actualiza: porcentaje_avance, estado, crea nota de avance, recalcula proyecto
     */
    public function guardarAvance()
    {
        AuthMiddleware::verificarSesion();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id_tarea = $_POST['id_tarea'] ?? 0;
        $porcentaje_nuevo = $_POST['porcentaje_nuevo'] ?? 0;
        $nota = $_POST['nota_desarrollador'] ?? '';

        // Validaciones
        if (empty($id_tarea) || $porcentaje_nuevo < 0 || $porcentaje_nuevo > 100) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        try {
            $tareaModel = new Tarea();
            require_once __DIR__ . '/../models/Proyecto.php';
            $proyectoModel = new Proyecto();

            // 1. Obtener tarea actual para conseguir porcentaje anterior
            $tarea = $tareaModel->obtenerPorId($id_tarea);
            if (!$tarea) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
                exit;
            }

            // 2. Guardar nota de avance en tabla notas_tareas
            $notaData = [
                'id_tarea' => $id_tarea,
                'porcentaje_anterior' => $tarea['porcentaje_avance'],
                'porcentaje_nuevo' => $porcentaje_nuevo,
                'nota_desarrollador' => $nota
            ];
            $resultadoNota = $tareaModel->guardarNotaTarea($notaData);

            if (!$resultadoNota['exito']) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al guardar la nota']);
                exit;
            }

            // 3. Actualizar porcentaje_avance y estado de la tarea
            $nuevoEstado = 'Pendiente';
            if ($porcentaje_nuevo > 0 && $porcentaje_nuevo < 100) {
                $nuevoEstado = 'En Progreso';
            } elseif ($porcentaje_nuevo >= 100) {
                $nuevoEstado = 'Completado';
            }

            $datosActualizar = [
                'titulo' => $tarea['titulo'],
                'descripcion' => $tarea['descripcion'],
                'id_usuario' => $tarea['id_usuario'],
                'area_asignada' => $tarea['area_asignada'],
                'fecha_inicio' => $tarea['fecha_inicio'],
                'fecha_limite' => $tarea['fecha_limite'],
                'estado' => $nuevoEstado,
                'porcentaje_avance' => $porcentaje_nuevo
            ];

            $resultadoActualizacion = $tareaModel->actualizar($id_tarea, $datosActualizar);

            if (!$resultadoActualizacion['exito']) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la tarea']);
                exit;
            }

            // 4. Recalcular porcentaje del proyecto
            $id_proyecto = $tarea['id_proyecto'];
            $proyectoModel->actualizarProgreso($id_proyecto);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Avance guardado exitosamente',
                'porcentaje_nuevo' => $porcentaje_nuevo,
                'estado' => $nuevoEstado
            ]);
            exit;

        } catch (Exception $e) {
            error_log("Error en guardarAvance: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
            exit;
        }
    }

    /**
     * NUEVA FUNCIÓN: Obtener tareas completadas de un usuario
     */
    public function obtenerTareasCompletadas()
    {
        AuthMiddleware::verificarSesion();

        $id_usuario = $_GET['id_usuario'] ?? ($_SESSION['usuario']['id_usuario'] ?? 0);

        if (empty($id_usuario)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
            exit;
        }

        try {
            $tareaModel = new Tarea();

            $sql = "SELECT 
                        t.id_tarea,
                        t.titulo,
                        t.descripcion,
                        t.area_asignada,
                        t.fecha_limite,
                        t.estado,
                        t.porcentaje_avance,
                        p.nombre as proyecto_nombre,
                        COUNT(n.id_nota) as total_notas
                    FROM tareas t
                    INNER JOIN proyectos p ON t.id_proyecto = p.id_proyecto
                    LEFT JOIN notas_tareas n ON t.id_tarea = n.id_tarea
                    WHERE t.id_usuario = :id_usuario AND t.porcentaje_avance = 100
                    GROUP BY t.id_tarea
                    ORDER BY t.fecha_limite DESC";

            // Acceso a la conexión a través de Reflection
            $reflection = new \ReflectionClass('Tarea');
            $prop = $reflection->getProperty('conn');
            $prop->setAccessible(true);
            $conn = $prop->getValue($tareaModel);

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $tareasCompletadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'tareas_completadas' => $tareasCompletadas
            ]);
            exit;

        } catch (Exception $e) {
            error_log("Error en obtenerTareasCompletadas: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
            exit;
        }
    }

    /**
     * NUEVA FUNCIÓN: Obtener notas de una tarea
     */
    public function obtenerNotasTarea()
    {
        AuthMiddleware::verificarSesion();

        $id_tarea = $_GET['id_tarea'] ?? 0;

        if (empty($id_tarea)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID de tarea inválido']);
            exit;
        }

        try {
            $tareaModel = new Tarea();
            $notas = $tareaModel->obtenerNotasTarea($id_tarea);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'notas' => $notas
            ]);
            exit;

        } catch (Exception $e) {
            error_log("Error en obtenerNotasTarea: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
            exit;
        }
    }
}
?>