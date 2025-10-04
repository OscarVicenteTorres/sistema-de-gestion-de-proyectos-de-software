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
            header("Location: /proyect-prub/proyecto_gestor/index.php?c=Auth&a=login");
            exit;
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
                header("Location: /proyect-prub/proyecto_gestor/index.php?c=Usuario&a=dashboardAdmin");
            } else if ($rolUsuario === 'Desarrollador') {
                header("Location: /proyect-prub/proyecto_gestor/index.php?c=Usuario&a=dashboardDesarrollador");
            } else if ($rolUsuario === 'Gestor de Proyecto') {
                header("Location: /proyect-prub/proyecto_gestor/index.php?c=Usuario&a=dashboardGestor");
            } else {
                header("Location: /proyect-prub/proyecto_gestor/index.php?c=Auth&a=login");
            }
            exit;
        }
    }

    /**
     * Obtener el usuario actual de la sesión
     */
    public static function getUsuarioActual() {
        return $_SESSION['usuario'] ?? null;
    }
}
