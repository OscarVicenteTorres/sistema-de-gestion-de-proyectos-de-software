<?php
require_once __DIR__ . "/../models/JustificacionTarea.php";
require_once __DIR__ . "/../models/Tarea.php";
require_once __DIR__ . '/../../core/BaseApiController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class JustificacionController extends BaseApiController
{
    private $tareaModel;
    private $justificacionModel;

    public function __construct()
    {
        $this->tareaModel = new Tarea();
        $this->justificacionModel = new JustificacionTarea();
    }

    /**
     * NUEVA FUNCIÓN: Guardar justificación de extensión de tarea
     * Llamada desde el dashboard del desarrollador cuando marca "No pude completar la tarea"
     */
    public function guardarJustificacion()
    {
        AuthMiddleware::verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id_tarea = $_POST['id_tarea'] ?? 0;
        $motivo = $_POST['motivo'] ?? '';
        $nueva_fecha_limite = $_POST['nueva_fecha_limite'] ?? '';
        $porcentaje_actual = $_POST['porcentaje_actual'] ?? 0;

        // Validaciones
        if (empty($id_tarea) || empty($motivo) || empty($nueva_fecha_limite)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Campos requeridos incompletos']);
            exit;
        }

        // Validar formato de fecha
        $fecha = \DateTime::createFromFormat('Y-m-d', $nueva_fecha_limite);
        if (!$fecha || $fecha->format('Y-m-d') !== $nueva_fecha_limite) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
            exit;
        }

        try {
            // 1. Verificar que la tarea existe
            $tarea = $this->tareaModel->obtenerPorId($id_tarea);
            if (!$tarea) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
                exit;
            }

            // 2. Crear justificación en base de datos
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

            // Acceso a la conexión a través de Reflection
            $reflection = new \ReflectionClass('Tarea');
            $prop = $reflection->getProperty('conn');
            $prop->setAccessible(true);
            $conn = $prop->getValue($this->tareaModel);

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_tarea', $id_tarea, PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->bindParam(':nueva_fecha_limite', $nueva_fecha_limite, PDO::PARAM_STR);

            $resultado = $stmt->execute();

            if (!$resultado) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al guardar la justificación']);
                exit;
            }

            $id_justificacion = $conn->lastInsertId();

            // 3. Crear una notificación para el administrador
            $sql_notif = "INSERT INTO notificaciones (
                            id_usuario,
                            mensaje,
                            tipo,
                            leida,
                            fecha
                        ) VALUES (
                            :id_usuario,
                            :mensaje,
                            'Tarea',
                            0,
                            NOW()
                        )";

            // Obtener ID del usuario creador (Admin principal) - normalmente ID 1
            $usuarioAdmin = 1; // O se puede obtener de configuración

            $stmt_notif = $conn->prepare($sql_notif);
            $mensaje = "Nueva solicitud de justificación: '{$tarea['titulo']}' - Nueva fecha: {$nueva_fecha_limite}";
            $stmt_notif->bindParam(':id_usuario', $usuarioAdmin, PDO::PARAM_INT);
            $stmt_notif->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
            $stmt_notif->execute();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Justificación enviada exitosamente al administrador',
                'id_justificacion' => $id_justificacion
            ]);
            exit;

        } catch (Exception $e) {
            error_log("Error en guardarJustificacion: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
            exit;
        }
    }

    /**
     * NUEVA FUNCIÓN: Obtener justificaciones de un usuario
     */
    public function obtenerJustificacionesUsuario()
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
            $sql = "SELECT 
                        jt.id_justificacion_tarea,
                        jt.id_tarea,
                        jt.motivo,
                        jt.nueva_fecha_limite,
                        jt.estado,
                        jt.fecha_solicitud,
                        jt.comentarios_admin,
                        t.titulo as tarea_titulo,
                        t.porcentaje_avance,
                        p.nombre as proyecto_nombre
                    FROM justificaciones_tareas jt
                    INNER JOIN tareas t ON jt.id_tarea = t.id_tarea
                    INNER JOIN proyectos p ON t.id_proyecto = p.id_proyecto
                    WHERE t.id_usuario = :id_usuario
                    ORDER BY jt.fecha_solicitud DESC";

            // Acceso a la conexión a través de Reflection
            $reflection = new \ReflectionClass('Tarea');
            $prop = $reflection->getProperty('conn');
            $prop->setAccessible(true);
            $conn = $prop->getValue($this->tareaModel);

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $justificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'justificaciones' => $justificaciones
            ]);
            exit;

        } catch (Exception $e) {
            error_log("Error en obtenerJustificacionesUsuario: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
            exit;
        }
    }
}
