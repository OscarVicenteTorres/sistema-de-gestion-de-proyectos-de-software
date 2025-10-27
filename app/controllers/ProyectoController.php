<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Proyecto.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ProyectoController extends Controller
{
    private $db;
    /**
     * Listar todos los proyectos en formato JSON (para AJAX)
     */
    public function listarAjax()
    {
        AuthMiddleware::verificarSesion();
        $proyectoModel = new Proyecto();
        $proyectos = $proyectoModel->obtenerTodosConEncargados();
        header('Content-Type: application/json');
        echo json_encode($proyectos);
        exit;
    }

    /**
     * Ver todos los proyectos (lista)
     */
    public function index()
    {
        AuthMiddleware::verificarSesion();

        $proyectoModel = new Proyecto();
        $proyectos = $proyectoModel->obtenerTodosConEncargados();

        $this->render('admin/dashboard', ['proyectos' => $proyectos]);
    }

    /**
     * Crear un nuevo proyecto
     */
    public function crear()
    {
        AuthMiddleware::verificarRol(['Admin', 'Gestor de Proyecto']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyectoModel = new Proyecto();

            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                'fecha_fin' => $_POST['fecha_fin'] ?? null,
                'estado' => 'Pendiente', // Estado inicial por defecto
                'categoria' => $_POST['categoria'] ?? null,
                'porcentaje_avance' => 0, // Progreso inicial
                'cliente' => $_POST['cliente'] ?? null,
                'recursos' => $_POST['recursos'] ?? null,
                'tecnologias' => $_POST['tecnologias'] ?? null,
                'id_usuario_creador' => $_SESSION['usuario']['id_usuario']
            ];

            $resultado = $proyectoModel->crear($datos);

            header('Content-Type: application/json');
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Proyecto creado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el proyecto']);
            }
            exit;
        }
    }

    /**
     * Actualizar un proyecto existente
     */
    public function actualizar()
    {
        AuthMiddleware::verificarRol(['Admin', 'Gestor de Proyecto']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyectoModel = new Proyecto();
            $id = $_GET['id'] ?? 0;

            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                'fecha_fin' => $_POST['fecha_fin'] ?? null,
                'estado' => $_POST['estado'] ?? 'Pendiente',
                'categoria' => $_POST['categoria'] ?? null,
                'porcentaje_avance' => $_POST['porcentaje_avance'] ?? 0,
                'cliente' => $_POST['cliente'] ?? null,
                'recursos' => $_POST['recursos'] ?? null,
                'tecnologias' => $_POST['tecnologias'] ?? null
            ];

            $resultado = $proyectoModel->actualizar($id, $datos);

            header('Content-Type: application/json');
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Proyecto actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el proyecto']);
            }
            exit;
        }
    }

    /**
     * Eliminar un proyecto
     */
    public function eliminar()
    {
        AuthMiddleware::verificarRol(['Admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyectoModel = new Proyecto();
            $id = $_GET['id'] ?? 0;

            $resultado = $proyectoModel->eliminar($id);

            header('Content-Type: application/json');
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Proyecto eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el proyecto']);
            }
            exit;
        }
    }

    /**
     * Obtener detalles de un proyecto
     */
    public function obtenerDetalles()
    {
        AuthMiddleware::verificarSesion();

        $id = $_GET['id'] ?? 0;
        $proyectoModel = new Proyecto();
        $proyecto = $proyectoModel->obtenerPorId($id);

        header('Content-Type: application/json');
        if ($proyecto) {
            // Formatear fechas
            if ($proyecto['fecha_inicio']) {
                $proyecto['fecha_inicio'] = date('Y-m-d', strtotime($proyecto['fecha_inicio']));
            }
            if ($proyecto['fecha_fin']) {
                $proyecto['fecha_fin'] = date('Y-m-d', strtotime($proyecto['fecha_fin']));
            }

            echo json_encode([
                'success' => true,
                'proyecto' => $proyecto
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Proyecto no encontrado'
            ]);
        }
        exit;
    }


    /**
     * Exportar proyectos
     */
    public function exportar()
    {
        AuthMiddleware::verificarRol(['Admin', 'Gestor de Proyecto']);

        $proyectoModel = new Proyecto();

        // Obtener todos los proyectos completados
        $proyectosCompletados = $proyectoModel->obtenerCompletados();

        // Obtener estadísticas generales
        $estadisticas = $proyectoModel->obtenerEstadisticas();

        // Enviar ambos a la vista
        $this->render('admin/exportar', [
            'proyectos' => $proyectosCompletados,
            'estadisticas' => $estadisticas
        ]);
    }

    public function obtenerProyectosPorUsuario($id_usuario)
    {
        $sql = "SELECT 
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
            GROUP BY p.id_proyecto
            ORDER BY p.fecha_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function dashboard()
    {
        session_start();
        $usuarioId = $_SESSION['usuario']['id_usuario'] ?? null;

        if (!$usuarioId) {
            header('Location: index.php?c=Auth&a=login');
            exit;
        }

        $proyectoModel = new Proyecto();
        $tareaModel = new Tarea();

        // Obtener proyectos asignados al usuario
        $proyectos = $proyectoModel->obtenerProyectosPorUsuario($usuarioId);

        // Obtener tareas de cada proyecto
        foreach ($proyectos as &$proyecto) {
            $proyecto['tareas'] = $tareaModel->obtenerTareasPorProyecto($proyecto['id_proyecto']);
        }

        include __DIR__ . '/../views/desarrollador/dashboard.php';
    }
}
