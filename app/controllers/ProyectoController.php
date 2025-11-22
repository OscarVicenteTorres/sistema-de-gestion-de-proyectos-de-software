<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Proyecto.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../libraries/dompdf/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
     * NUEVA FUNCIÓN: Obtener estadísticas de proyectos en formato JSON
     */
    public function estadisticasAjax()
    {
        AuthMiddleware::verificarSesion();
        $proyectoModel = new Proyecto();
        $estadisticas = $proyectoModel->obtenerEstadisticas();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'estadisticas' => $estadisticas
        ]);
        exit;
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

        // Obtener TODOS los proyectos sin filtrar
        $proyectos = $proyectoModel->listarParaExportar();

        // Obtener estadísticas generales
        $estadisticas = $proyectoModel->obtenerEstadisticas();

        // Enviar ambos a la vista
        $this->render('admin/exportar', [
            'proyectos' => $proyectos,
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

    /**
     * Obtener proyectos para la tabla de exportación (JSON)
     */
    public function obtenerProyectosJSON()
    {
        AuthMiddleware::verificarSesion();

        $proyectoModel = new Proyecto();
        $proyectos = $proyectoModel->listarParaExportar();

        header('Content-Type: application/json');
        echo json_encode($proyectos);
        exit;
    }

    public function exportarpdf()
    {
        AuthMiddleware::verificarRol(['Admin', 'Gestor de Proyecto']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // 1. Cargar Dompdf correctamente
            require_once __DIR__ . '/../../libraries/dompdf/vendor/autoload.php';
            $dompdf = new Dompdf();

            $ids = $_POST['proyectos'] ?? [];
            $formato = $_POST['formato'] ?? 'PDF';

            if (empty($ids)) {
                $_SESSION['error'] = 'Debes seleccionar al menos un proyecto';
                header('Location: ?c=Proyecto&a=exportar');
                exit;
            }

            $proyectoModel = new Proyecto();
            $proyectos = $proyectoModel->obtenerProyectosPorIds($ids);

            if ($formato === 'PDF') {

                // Generar HTML para el PDF
                $html = '';
                foreach ($proyectos as $proyecto) {
                    $tareas = $proyectoModel->obtenerTareasPorProyecto($proyecto['id_proyecto']);
                    $hitos = []; // No usar hitos, tabla no existe

                    ob_start();
                    include __DIR__ . '/../views/admin/plantilla_exportar_moderno.php';
                    $html .= ob_get_clean();
                }

                // Enviar al PDF
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream('Proyectos.pdf', ['Attachment' => true]);
                exit;
            } elseif ($formato === 'CSV') {

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="proyectos.csv"');

                $output = fopen('php://output', 'w');
                fputcsv($output, ['Nombre', 'Descripción', 'Fecha Inicio', 'Fecha Fin', 'Estado', 'Avance', 'Área', 'Cliente']);

                foreach ($proyectos as $p) {
                    fputcsv($output, [
                        $p['nombre'],
                        $p['descripcion'],
                        $p['fecha_inicio'],
                        $p['fecha_fin'],
                        $p['estado'],
                        $p['porcentaje_avance'] ?? 0,
                        $p['categoria'] ?? $p['area'] ?? '',
                        $p['cliente'] ?? ''
                    ]);
                }
                fclose($output);
                exit;
            }
        }
    }
}
