<?php
require_once __DIR__ . '/../../core/BaseApiController.php';
require_once __DIR__ . '/../models/DashboardDesarrollador.php';

/**
 * CONTROLADOR: DashboardDesarrolladorController
 * 
 * Maneja todas las operaciones del dashboard del desarrollador:
 * - Mostrar proyectos y tareas
 * - Guardar avances
 * - Obtener historial de avances
 * - Obtener tareas completadas
 * - Guardar justificaciones
 */
class DashboardDesarrolladorController extends BaseApiController
{
    private DashboardDesarrollador $model;

    public function __construct()
    {
        $this->model = new DashboardDesarrollador();
    }

    /**
     * Página principal del dashboard del desarrollador
     */
    public function index(): void
    {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validar que el usuario esté autenticado
        if (!isset($_SESSION['usuario'])) {
            header('Location: ?c=Auth&a=login');
            exit;
        }

        // Validar que sea desarrollador (id_rol = 2)
        if ($_SESSION['usuario']['id_rol'] != 2) {
            header('Location: ?c=Auth&a=login');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id_usuario'];

        // Obtener proyectos y tareas
        $proyectos = $this->model->obtenerProyectos($usuarioId);

        // Enriquecer proyectos con tareas
        foreach ($proyectos as &$proyecto) {
            $proyecto['tareas'] = $this->model->obtenerTareasPorProyecto($proyecto['id_proyecto']);
        }

        // Renderizar vista
        $this->render('desarrollador/dashboard', [
            'proyectos' => $proyectos
        ]);
    }

    /**
     * API: Obtener historial de avances de una tarea (JSON)
     */
    public function obtenerNotasTarea(): void
    {
        // Iniciar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validar autenticación
        if (!isset($_SESSION['usuario'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        $id_tarea = $_GET['id_tarea'] ?? null;

        if (!$id_tarea) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de tarea no proporcionado']);
            exit;
        }

        $notas = $this->model->obtenerNotasTarea($id_tarea);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'notas' => $notas
        ]);
    }

    /**
     * API: Guardar avance de tarea (JSON)
     */
    public function guardarAvance(): void
    {
        // Iniciar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validar autenticación
        if (!isset($_SESSION['usuario'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        // Obtener datos del POST
        $datos = [
            'id_tarea' => $_POST['id_tarea'] ?? null,
            'porcentaje_nuevo' => $_POST['porcentaje_nuevo'] ?? 0,
            'nota_desarrollador' => $_POST['nota_desarrollador'] ?? ''
        ];

        if (!$datos['id_tarea']) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de tarea no proporcionado']);
            exit;
        }

        // Guardar avance
        $resultado = $this->model->guardarAvance($datos);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $resultado['exito'],
            'message' => $resultado['mensaje']
        ]);
    }

    /**
     * API: Obtener tareas completadas (JSON)
     */
    public function obtenerTareasCompletadas(): void
    {
        // Iniciar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validar autenticación
        if (!isset($_SESSION['usuario'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id_usuario'];
        $tareas = $this->model->obtenerTareasCompletadas($usuarioId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'tareas_completadas' => $tareas
        ]);
    }

    /**
     * API: Guardar justificación (JSON)
     */
    public function guardarJustificacion(): void
    {
        // Iniciar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validar autenticación
        if (!isset($_SESSION['usuario'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        // Obtener datos
        $datos = [
            'id_tarea' => $_POST['id_tarea'] ?? null,
            'motivo' => $_POST['motivo'] ?? '',
            'nueva_fecha_limite' => $_POST['nueva_fecha_limite'] ?? null
        ];

        if (!$datos['id_tarea'] || !$datos['nueva_fecha_limite']) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }

        // Guardar justificación
        $resultado = $this->model->guardarJustificacion($datos);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $resultado['exito'],
            'message' => $resultado['mensaje']
        ]);
    }
}
