<?php
// Configuración general de la aplicación

// Ruta base del proyecto en el servidor
define('BASE_URL', '/proyect-prub/proyecto_gestor');

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
