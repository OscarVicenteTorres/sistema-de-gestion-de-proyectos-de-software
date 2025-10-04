<?php
session_start();

// Cargar configuración
require_once __DIR__ . '/app/config/config.php';

// Cargar clases base
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';

// Iniciar en login por defecto
$router = new Router();
$router->run();
