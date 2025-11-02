<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Asistencia.php';

class AsistenciaController extends Controller
{
    private $asistenciaModel;

    public function __construct()
    {
        $this->asistenciaModel = new Asistencia();
    }

    /**
     * Página principal del registro de asistencias
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header('Location: ?c=Auth&a=login');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        $usuario_id = $_SESSION['usuario']['id_usuario'];


        // Obtener historial del usuario
        $historial = $this->asistenciaModel->obtenerHistorial($usuario_id);

        // Enviar datos a la vista
        $this->render('desarrollador/asistencia', [
            'usuario' => $usuario,
            'historial' => $historial
        ]);
    }

    /**
     * Registrar la entrada del usuario
     */
    public function registrarEntrada()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header('Location: ?c=Auth&a=login');
            exit;
        }

        $usuario_id = $_SESSION['usuario']['id_usuario'];
        $resultado = $this->asistenciaModel->registrarEntrada($usuario_id);

        if ($resultado) {
            // Si la entrada se registró exitosamente, ir directamente al dashboard
            header('Location: ?c=Usuario&a=dashboard');
            exit;
        } else {
            // Si ya registró entrada, mostrar alerta pero seguir en asistencia
            $_SESSION['mensaje_error'] = "Ya registraste tu entrada hoy.";
            header('Location: ?c=Asistencia&a=index');
            exit;
        }
    }

    /**
     * Registrar la salida del usuario
     */
    public function registrarSalida()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header('Location: ?c=Auth&a=login');
            exit;
        }

        $usuario_id = $_SESSION['usuario']['id_usuario'];
        $resultado = $this->asistenciaModel->registrarSalida($usuario_id);

        if ($resultado) {
            $_SESSION['mensaje_exito'] = "¡Salida registrada correctamente! Que tengas un excelente día.";
        } else {
            $_SESSION['mensaje_error'] = "Error al registrar la salida. Intenta nuevamente.";
        }

        header('Location: ?c=Asistencia&a=index');
        exit;
    }
}
