<?php
require_once __DIR__ . "/../models/JustificacionTarea.php";
require_once __DIR__ . "/../models/Tarea.php";
require_once __DIR__ . '/../../core/BaseApiController.php';

/**
 * CONTROLADOR JUSTIFICACION - BACKEND PARA ADMINISTRADOR
 * 
 * Maneja las justificaciones de tareas enviadas por desarrolladores.
 * El administrador puede aprobar o rechazar solicitudes de extensión de tiempo.
 * Todos los métodos retornan JSON para fácil integración con frontend.
 */
class JustificacionController extends BaseApiController {
    private $justificacionModel;
    private $tareaModel;

    public function __construct() {
        $this->justificacionModel = new JustificacionTarea();
        $this->tareaModel = new Tarea();
        // NOTA: no verificamos rol aquí para permitir que los desarrolladores
        // creen justificaciones desde su dashboard. Los métodos que requieren
        // permisos de admin llamarán a $this->verificarAdmin() explícitamente.
    }

    /**
     * Lista todas las justificaciones (dashboard del admin)
     * URL: /index.php?c=Justificacion&a=index
     * Método: GET
     */
    public function index() {
        // Admin only
        $this->verificarAdmin();
        try {
            // Obtener filtros de la URL
            $filtros = [
                'estado' => $_GET['estado'] ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                'tarea_id' => $_GET['tarea_id'] ?? ''
            ];
            
            // Remover filtros vacíos
            $filtros = array_filter($filtros, function($valor) {
                return !empty($valor);
            });
            
            // Obtener justificaciones y estadísticas
            // Aceptamos filtro opcional proyecto_id para mostrar solo justificaciones del proyecto
            if (!empty($_GET['proyecto_id']) && is_numeric($_GET['proyecto_id'])) {
                $filtros['proyecto_id'] = intval($_GET['proyecto_id']);
            }
            $justificaciones = $this->justificacionModel->obtenerTodas($filtros);
            $estadisticas = $this->justificacionModel->obtenerEstadisticas();
            $opciones = $this->justificacionModel->obtenerOpcionesFormulario();
            
            // Si es petición AJAX, devolver JSON
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'exito' => true,
                    'justificaciones' => $justificaciones,
                    'estadisticas' => $estadisticas,
                    'opciones' => $opciones,
                    'filtros_aplicados' => $filtros
                ]);
                exit;
            }
            
            // Para vistas normales, cargar vista con datos
            require __DIR__ . "/../views/admin/justificaciones.php";
            
        } catch (Exception $e) {
            error_log("Error en JustificacionController::index: " . $e->getMessage());
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Error interno del servidor']);
            } else {
                $error = "Error al cargar las justificaciones";
                require __DIR__ . "/../views/admin/justificaciones.php";
            }
        }
    }

    /**
     * Muestra detalle de una justificación específica
     * URL: /index.php?c=Justificacion&a=detalle&id=123
     * Método: GET
     */
    public function detalle() {
        // Admin only
        $this->verificarAdmin();
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'ID de justificación requerido']);
            } else {
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        try {
            $justificacion = $this->justificacionModel->obtenerPorId($id);
            
            if (!$justificacion) {
                if ($this->esAjax()) {
                    header('Content-Type: application/json');
                    echo json_encode(['exito' => false, 'mensaje' => 'Justificación no encontrada']);
                } else {
                    redirect('Justificacion', 'index');
                }
                return;
            }
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'exito' => true,
                    'justificacion' => $justificacion
                ]);
                exit;
            }
            
            require __DIR__ . "/../views/admin/detalle_justificacion.php";
            
        } catch (Exception $e) {
            error_log("Error en JustificacionController::detalle: " . $e->getMessage());
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Error al cargar la justificación']);
            } else {
                redirect('Justificacion', 'index');
            }
        }
    }

    /**
     * Aprueba una justificación
     * URL: /index.php?c=Justificacion&a=aprobar
     * Método: POST
     */
    public function aprobar() {
        // Admin only
        $this->verificarAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            } else {
                redirect('Justificacion', 'index');
            }
            return;
        }
        
    // Acepta form-data: id, comentarios y opcionalmente nueva_fecha_confirmada (YYYY-MM-DD)
    $id = $_POST['id'] ?? null;
    $comentarios = $_POST['comentarios'] ?? '';
    $nueva_fecha_confirmada = $_POST['nueva_fecha_confirmada'] ?? null;
        $admin_id = $_SESSION['usuario']['id_usuario'];
        
        if (!$id) {
            $respuesta = ['exito' => false, 'mensaje' => 'ID de justificación requerido'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        try {
            $resultado = $this->justificacionModel->aprobar($id, $admin_id, $comentarios, $nueva_fecha_confirmada);
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($resultado);
            } else {
                if ($resultado['exito']) {
                    $_SESSION['mensaje_exito'] = $resultado['mensaje'];
                } else {
                    $_SESSION['mensaje_error'] = $resultado['mensaje'];
                }
                redirect('Justificacion', 'index');
            }
            
        } catch (Exception $e) {
            error_log("Error en JustificacionController::aprobar: " . $e->getMessage());
            
            $respuesta = ['exito' => false, 'mensaje' => 'Error interno del servidor'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                $_SESSION['mensaje_error'] = $respuesta['mensaje'];
                redirect('Justificacion', 'index');
            }
        }
    }

    /**
     * Crea una justificación (para desarrolladores)
     * URL: /index.php?c=Justificacion&a=crear
     * Método: POST
     */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', [], 405);
        }

        if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']['id_usuario'])) {
            $this->jsonError('Acceso denegado: inicie sesión', [], 401);
        }

        $datos = $this->getInput();
        $this->validarCampos($datos, ['id_tarea', 'motivo', 'nueva_fecha_limite']);

        $this->ejecutarOperacion(function() use ($datos) {
            $tarea = $this->tareaModel->obtenerPorId($datos['id_tarea']);
            if (!$tarea) {
                return ['exito' => false, 'mensaje' => 'Tarea no encontrada'];
            }

            $usuarioId = $_SESSION['usuario']['id_usuario'];
            if ((int)$tarea['id_usuario'] !== (int)$usuarioId) {
                return ['exito' => false, 'mensaje' => 'Solo el desarrollador asignado puede solicitar extensión para esta tarea'];
            }

            return $this->justificacionModel->crear($datos);
        });
    }

    /**
     * Rechaza una justificación
     * URL: /index.php?c=Justificacion&a=rechazar
     * Método: POST
     */
    public function rechazar() {
        // Admin only
        $this->verificarAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            } else {
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        $id = $_POST['id'] ?? null;
        $comentarios = $_POST['comentarios'] ?? '';
        $admin_id = $_SESSION['usuario']['id_usuario'];
        
        if (!$id) {
            $respuesta = ['exito' => false, 'mensaje' => 'ID de justificación requerido'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        if (empty(trim($comentarios))) {
            $respuesta = ['exito' => false, 'mensaje' => 'Debe proporcionar una razón para el rechazo'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                $_SESSION['mensaje_error'] = $respuesta['mensaje'];
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        try {
            $resultado = $this->justificacionModel->rechazar($id, $admin_id, $comentarios);
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($resultado);
            } else {
                if ($resultado['exito']) {
                    $_SESSION['mensaje_exito'] = $resultado['mensaje'];
                } else {
                    $_SESSION['mensaje_error'] = $resultado['mensaje'];
                }
                redirect('Justificacion', 'index');
            }
            
        } catch (Exception $e) {
            error_log("Error en JustificacionController::rechazar: " . $e->getMessage());
            
            $respuesta = ['exito' => false, 'mensaje' => 'Error interno del servidor'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                $_SESSION['mensaje_error'] = $respuesta['mensaje'];
                redirect('Justificacion', 'index');
            }
        }
    }

    /**
     * Procesa múltiples justificaciones en lote (aprobar o rechazar varias)
     * URL: /index.php?c=Justificacion&a=procesarLote
     * Método: POST
     */
    public function procesarLote() {
        // Admin only
        $this->verificarAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            } else {
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        $ids = $_POST['ids'] ?? [];
        $accion = $_POST['accion'] ?? '';
        $comentarios = $_POST['comentarios'] ?? '';
        $admin_id = $_SESSION['usuario']['id_usuario'];
        
        if (empty($ids) || !is_array($ids)) {
            $respuesta = ['exito' => false, 'mensaje' => 'Seleccione al menos una justificación'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                $_SESSION['mensaje_error'] = $respuesta['mensaje'];
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        if (!in_array($accion, ['aprobar', 'rechazar'])) {
            $respuesta = ['exito' => false, 'mensaje' => 'Acción no válida'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        if ($accion === 'rechazar' && empty(trim($comentarios))) {
            $respuesta = ['exito' => false, 'mensaje' => 'Debe proporcionar una razón para el rechazo'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                $_SESSION['mensaje_error'] = $respuesta['mensaje'];
                redirect('Justificacion', 'index');
            }
            return;
        }
        
        try {
            $procesadas = 0;
            $errores = 0;
            
            foreach ($ids as $id) {
                if ($accion === 'aprobar') {
                    $resultado = $this->justificacionModel->aprobar($id, $admin_id, $comentarios);
                } else {
                    $resultado = $this->justificacionModel->rechazar($id, $admin_id, $comentarios);
                }
                
                if ($resultado['exito']) {
                    $procesadas++;
                } else {
                    $errores++;
                }
            }
            
            $mensaje = "Se procesaron $procesadas justificaciones";
            if ($errores > 0) {
                $mensaje .= " ($errores errores)";
            }
            
            $respuesta = [
                'exito' => $procesadas > 0,
                'mensaje' => $mensaje,
                'procesadas' => $procesadas,
                'errores' => $errores
            ];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                if ($respuesta['exito']) {
                    $_SESSION['mensaje_exito'] = $respuesta['mensaje'];
                } else {
                    $_SESSION['mensaje_error'] = $respuesta['mensaje'];
                }
                redirect('Justificacion', 'index');
            }
            
        } catch (Exception $e) {
            error_log("Error en JustificacionController::procesarLote: " . $e->getMessage());
            
            $respuesta = ['exito' => false, 'mensaje' => 'Error interno del servidor'];
            
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode($respuesta);
            } else {
                $_SESSION['mensaje_error'] = $respuesta['mensaje'];
                redirect('Justificacion', 'index');
            }
        }
    }

    /**
     * Obtiene estadísticas para dashboard
     * URL: /index.php?c=Justificacion&a=estadisticas
     * Método: GET
     */
    public function estadisticas() {
        // Admin only
        $this->verificarAdmin();
        try {
            $estadisticas = $this->justificacionModel->obtenerEstadisticas();
            
            header('Content-Type: application/json');
            echo json_encode([
                'exito' => true,
                'estadisticas' => $estadisticas
            ]);
            
        } catch (Exception $e) {
            error_log("Error en JustificacionController::estadisticas: " . $e->getMessage());
            
            header('Content-Type: application/json');
            echo json_encode(['exito' => false, 'mensaje' => 'Error al obtener estadísticas']);
        }
    }

}