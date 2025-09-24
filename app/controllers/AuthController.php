<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/User.php";

session_start();

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    public function login($email, $password) {
        $user = $this->userModel->login($email, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            if ($user['role'] === 'admin') {
                header("Location: /app/views/dashboard_admin.php");
            } else {
                header("Location: /app/views/dashboard_dev.php");
            }
            exit;
        } else {
            $error = "Correo o contraseña incorrectos";
            include __DIR__ . "/../views/login.php";
        }
    }

    public function logout() {
        session_destroy();
        header("Location: /app/views/login.php");
        exit;
    }
}
?>