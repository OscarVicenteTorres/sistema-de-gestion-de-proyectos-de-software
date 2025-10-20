<?php
require_once __DIR__ . "/../models/JustificacionTarea.php";
require_once __DIR__ . '/../../core/Controller.php';

/**
 * CONTROLADOR JUSTIFICACION - BACKEND PARA ADMINISTRADOR
 * 
 * Maneja las justificaciones de tareas enviadas por desarrolladores.
 * El administrador puede aprobar o rechazar solicitudes de extensión de tiempo.
 * Todos los métodos retornan JSON para fácil integración con frontend.
 */
class JustificacionController extends Controller {
    private $justificacionModel;

    public function __construct() {
        $this->justificacionModel = new JustificacionTarea();
        
        // Verificar que el usuario sea administrador
        $this->verificarAdmin();
    }

    /**
     * Lista todas las justificaciones (dashboard del admin)
     * URL: /index.php?c=Justificacion&a=index
     * Método: GET
     */
    public function index() {
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
        
        try {
            $resultado = $this->justificacionModel->aprobar($id, $admin_id, $comentarios);
            
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
     * Rechaza una justificación
     * URL: /index.php?c=Justificacion&a=rechazar
     * Método: POST
     */
    public function rechazar() {
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

    /**
     * Verifica que el usuario actual sea administrador
     */
    private function verificarAdmin() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Admin') {
            if ($this->esAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Acceso denegado']);
            } else {
                redirect('Auth', 'login');
            }
            exit;
        }
    }

    /**
     * Determina si la petición es AJAX
     */
    private function esAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}