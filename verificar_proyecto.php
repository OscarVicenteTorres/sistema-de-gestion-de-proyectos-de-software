<?php
/**
 * SCRIPT DE VERIFICACIÓN DEL PROYECTO
 * Ejecutar este archivo para verificar que todo esté funcionando correctamente
 */

// Iniciar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 VERIFICACIÓN DEL PROYECTO</h1>";
echo "<hr>";

// 1. Verificar estructura de archivos
echo "<h2>📁 ESTRUCTURA DE ARCHIVOS</h2>";
$archivos_requeridos = [
    'index.php',
    'core/Router.php',
    'core/Controller.php',
    'app/config/config.php',
    'app/config/database.php',
    'app/controllers/AuthController.php',
    'app/controllers/UsuarioController.php',
    'app/controllers/AsistenciaController.php',
    'app/models/Usuario.php',
    'app/models/Asistencia.php',
    'app/middleware/AuthMiddleware.php',
    'app/views/auth/login.php',
    'app/views/desarrollador/dashboard.php',
    'app/views/desarrollador/asistencia.php',
    'public/css/login.css'
];

$errores_archivos = 0;
foreach ($archivos_requeridos as $archivo) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        echo "✅ {$archivo}<br>";
    } else {
        echo "❌ {$archivo} - <strong>FALTA</strong><br>";
        $errores_archivos++;
    }
}

echo "<p><strong>Archivos faltantes: {$errores_archivos}</strong></p>";

// 2. Verificar conexión a base de datos
echo "<h2>🗄️ BASE DE DATOS</h2>";
try {
    require_once __DIR__ . '/app/config/database.php';
    $conn = Database::connect();
    echo "✅ Conexión a base de datos exitosa<br>";
    
    // Verificar tablas
    $tablas_requeridas = ['usuarios', 'roles', 'proyectos', 'tareas'];
    foreach ($tablas_requeridas as $tabla) {
        $stmt = $conn->prepare("SHOW TABLES LIKE '{$tabla}'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla '{$tabla}' existe<br>";
        } else {
            echo "❌ Tabla '{$tabla}' - <strong>NO EXISTE</strong><br>";
        }
    }
    
    // Verificar usuarios de prueba
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "👥 Usuarios registrados: {$resultado['total']}<br>";
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}

// 3. Verificar configuración
echo "<h2>⚙️ CONFIGURACIÓN</h2>";
require_once __DIR__ . '/app/config/config.php';
echo "✅ BASE_URL: " . BASE_URL . "<br>";
echo "✅ FULL_URL: " . FULL_URL . "<br>";

// 4. Verificar clases principales
echo "<h2>🔧 CLASES PRINCIPALES</h2>";
$clases = [
    'Router' => __DIR__ . '/core/Router.php',
    'Controller' => __DIR__ . '/core/Controller.php',
    'Database' => __DIR__ . '/app/config/database.php',
    'Usuario' => __DIR__ . '/app/models/Usuario.php',
    'AuthController' => __DIR__ . '/app/controllers/AuthController.php',
];

foreach ($clases as $clase => $archivo) {
    if (file_exists($archivo)) {
        require_once $archivo;
        if (class_exists($clase)) {
            echo "✅ Clase {$clase} cargada correctamente<br>";
        } else {
            echo "❌ Clase {$clase} - <strong>NO SE PUDO CARGAR</strong><br>";
        }
    } else {
        echo "❌ Archivo {$archivo} - <strong>NO EXISTE</strong><br>";
    }
}

// 5. Verificar permisos
echo "<h2>🔐 PERMISOS</h2>";
if (is_writable(__DIR__)) {
    echo "✅ Directorio principal tiene permisos de escritura<br>";
} else {
    echo "⚠️ Directorio principal no tiene permisos de escritura<br>";
}

// 6. Verificar versión PHP
echo "<h2>🐘 PHP</h2>";
echo "✅ Versión PHP: " . PHP_VERSION . "<br>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✅ Versión PHP compatible<br>";
} else {
    echo "⚠️ Se recomienda PHP 7.4 o superior<br>";
}

// 7. Verificar extensiones PHP
$extensiones = ['pdo', 'pdo_mysql', 'json', 'session'];
foreach ($extensiones as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extensión {$ext} cargada<br>";
    } else {
        echo "❌ Extensión {$ext} - <strong>NO CARGADA</strong><br>";
    }
}

echo "<hr>";
echo "<h2>📋 RESUMEN</h2>";
echo "<p><strong>Estado del proyecto:</strong> ";
if ($errores_archivos == 0) {
    echo "<span style='color: green;'>✅ LISTO PARA USAR</span>";
} else {
    echo "<span style='color: red;'>❌ REQUIERE CORRECCIONES</span>";
}
echo "</p>";

echo "<h3>🚀 PRÓXIMOS PASOS</h3>";
echo "<ol>";
echo "<li>Ejecutar el script bd.sql en phpMyAdmin</li>";
echo "<li>Acceder a: <a href='" . FULL_URL . "'>" . FULL_URL . "</a></li>";
echo "<li>Usar credenciales: admin@empresa.com / 123456</li>";
echo "<li>Ejecutar prueba rápida: <a href='" . str_replace('/index.php', '/prueba_rapida.php', FULL_URL) . "'>Prueba Rápida</a></li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>Verificación completada el " . date('Y-m-d H:i:s') . "</em></p>";
?>