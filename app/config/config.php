<?php

//  ruta base automáticamente
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// la ruta base del proyecto
$basePath = str_replace('/index.php', '', $scriptName);
if (empty($basePath)) {
    // Si estamos en la raíz detectar la carpeta del proyecto
    $currentDir = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = $currentDir;
}

// Definir BASE_URL dinámicamente
define('BASE_URL', $basePath);
define('FULL_URL', $protocol . '://' . $host . $basePath);

// Rutas específicas
define('CSS_PATH', BASE_URL . '/public/css');
define('JS_PATH', BASE_URL . '/public/js');
define('IMG_PATH', BASE_URL . '/public/img');

// Configuración de la base de datos

// Función helper para generar URLs de recursos
function asset($path) {
    return BASE_URL . '/public/' . ltrim($path, '/');
}

function url($path = '') {
    return BASE_URL . '/index.php' . ($path ? '?' . $path : '');
}

function redirect($controller, $action = 'index', $params = []) {
    $url = url("c={$controller}&a={$action}");
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    header("Location: {$url}");
    exit;
}
