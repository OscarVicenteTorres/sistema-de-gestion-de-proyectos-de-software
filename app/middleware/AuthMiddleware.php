<?php
/**
 * Middleware para proteger rutas que requieren autenticación
 */
class AuthMiddleware {
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function verificarSesion() {
        if (!isset($_SESSION['usuario'])) {
            redirect('Auth', 'login');
        }
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function verificarRol($rolesPermitidos = []) {
        self::verificarSesion();
        
        $rolUsuario = $_SESSION['usuario']['rol'] ?? '';
        
        if (!in_array($rolUsuario, $rolesPermitidos)) {
            // Redirigir según el rol del usuario
            if ($rolUsuario === 'Admin') {
                redirect('Usuario', 'dashboardAdmin');
            } else if ($rolUsuario === 'Desarrollador') {
                redirect('Usuario', 'dashboardDesarrollador');
            } else if ($rolUsuario === 'Gestor de Proyecto') {
                redirect('Usuario', 'dashboardGestor');
            } else {
                redirect('Auth', 'login');
            }
        }
    }

    /**
     * Obtener el usuario actual de la sesión
     */
    public static function getUsuarioActual() {
        return $_SESSION['usuario'] ?? null;
    }
}
