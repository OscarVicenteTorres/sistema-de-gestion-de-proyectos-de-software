<?php
require_once __DIR__ . "/app/controllers/AuthController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $auth = new AuthController();
    $auth->login($email, $password);
} else {
    include __DIR__ . "/app/views/login.php";
}
?>
