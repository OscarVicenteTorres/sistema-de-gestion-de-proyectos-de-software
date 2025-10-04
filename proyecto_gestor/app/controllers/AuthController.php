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
                    header("Location: /proyect-prub/proyecto_gestor/index.php?c=Usuario&a=dashboardAdmin");
                } else if ($usuario['rol'] === 'Desarrollador') {
                    header("Location: /proyect-prub/proyecto_gestor/index.php?c=Usuario&a=dashboardDesarrollador");
                } else if ($usuario['rol'] === 'Gestor de Proyecto') {
                    header("Location: /proyect-prub/proyecto_gestor/index.php?c=Usuario&a=dashboardGestor");
                } else {
                    // Rol no reconocido, redirigir a login
                    header("Location: /proyect-prub/proyecto_gestor/index.php?c=Auth&a=login");
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
        header("Location: /proyect-prub/proyecto_gestor/index.php");
        exit;
    }
}
