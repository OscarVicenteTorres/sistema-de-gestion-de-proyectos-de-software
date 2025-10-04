<?php
require_once __DIR__ . "/../models/Usuario.php";
require_once __DIR__ . '/../../core/Controller.php';

class AuthController extends Controller
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = $_POST['correo'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->login($correo, $contrasena);

            if ($usuario) {
                $_SESSION['usuario'] = $usuario;

                // Redirigir según el rol del usuario
                if ($usuario['rol'] === 'Admin') {
                    redirect('Usuario', 'dashboardAdmin');
                } else if ($usuario['rol'] === 'Desarrollador') {
                    redirect('Usuario', 'dashboardDesarrollador');
                } else if ($usuario['rol'] === 'Gestor de Proyecto') {
                    redirect('Usuario', 'dashboardGestor');
                } else {
                    // Rol no reconocido, redirigir a login
                    redirect('Auth', 'login');
                }

                exit;
            } else {
                $error = "Correo o contraseña incorrectos.";
                require __DIR__ . "/../views/auth/login.php";
            }
        } else {
            require __DIR__ . "/../views/auth/login.php";
        }
    }

    public function logout()
    {
        session_destroy();
        redirect('Auth', 'login');
    }
}
