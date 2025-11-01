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
            $_SESSION['mensaje_exito'] = "¡Entrada confirmada! Tu entrada ha sido registrada con éxito.";

            // 🔹 Redirigir al dashboard del desarrollador
            header('Location: ?c=Usuario&a=dashboardDesarrollador');
            exit;
        } else {
            $_SESSION['mensaje_error'] = "Ya registraste tu entrada hoy.";

            // Si ya marcó asistencia, permanecer en la página
            header('Location: ?c=Usuario&a=dashboardDesarrollador');
            exit;
        }

        header('Location: ?c=Asistencia&a=index');
        exit;
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
            $_SESSION['mensaje'] = "Salida registrada correctamente.";
            header('Location: ?c=Auth&a=login');
            exit;
        } else {
            $_SESSION['mensaje'] = "Error al registrar la salida.";
            header('Location: ?c=Auth&a=login');
            exit;
        }

        header('Location: ?c=Asistencia&a=index');
        exit;
    }
}
