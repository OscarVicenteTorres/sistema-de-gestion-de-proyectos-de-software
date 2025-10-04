<?php
require_once __DIR__ . '/../../core/Controller.php';

class AsistenciaController extends Controller {

    public function index() {
        // Método principal de asistencia
        session_start();
        
        // Verificar autenticación
        if (!isset($_SESSION['usuario'])) {
            header('Location: ?c=Auth&a=login');
            exit;
        }
        
        $this->render('desarrollador/asistencia');
    }

    public function registro() {
        // Alias del método index
        $this->index();
    }
}

