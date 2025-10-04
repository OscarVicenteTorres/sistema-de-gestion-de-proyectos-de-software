<?php
// Configuración general de la aplicación

// Detectar ruta base automáticamente
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Extraer la ruta base del proyecto
$basePath = str_replace('/index.php', '', $scriptName);
if (empty($basePath)) {
    // Si estamos en la raíz, detectar la carpeta del proyecto
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

// Configuración de la base de datos (ya está en database.php)

// Función helper para generar URLs de recursos
function asset($path) {
    return BASE_URL . '/public/' . ltrim($path, '/');
}

// Función helper para generar URLs de la aplicación
function url($path = '') {
    return BASE_URL . '/index.php' . ($path ? '?' . $path : '');
}

// Función helper para redirecciones
function redirect($controller, $action = 'index', $params = []) {
    $url = url("c={$controller}&a={$action}");
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    header("Location: {$url}");
    exit;
}
