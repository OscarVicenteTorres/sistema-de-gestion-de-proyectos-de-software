<?php
/**
 * PRUEBA RÁPIDA DEL SISTEMA
 * Verificar que las rutas y configuración funcionen correctamente
 */

echo "<h1>🧪 PRUEBA RÁPIDA DEL SISTEMA</h1>";
echo "<hr>";

// 1. Verificar que los archivos principales existan
echo "<h2>📁 VERIFICACIÓN DE ARCHIVOS</h2>";

$archivos_criticos = [
    'index.php' => 'Punto de entrada',
    'app/config/config.php' => 'Configuración',
    'app/config/database.php' => 'Base de datos',
    'core/Router.php' => 'Enrutador',
    'core/Controller.php' => 'Controlador base'
];

$errores = 0;
foreach ($archivos_criticos as $archivo => $descripcion) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        echo "✅ {$descripcion} ({$archivo})<br>";
    } else {
        echo "❌ {$descripcion} ({$archivo}) - <strong>FALTANTE</strong><br>";
        $errores++;
    }
}

// 2. Cargar configuración
if ($errores === 0) {
    echo "<h2>⚙️ CONFIGURACIÓN</h2>";
    require_once __DIR__ . '/app/config/config.php';
    
    echo "✅ BASE_URL: " . BASE_URL . "<br>";
    echo "✅ FULL_URL: " . FULL_URL . "<br>";
    
    // 3. Probar funciones helper
    echo "<h2>🔧 FUNCIONES HELPER</h2>";
    echo "✅ asset('css/style.css'): " . asset('css/style.css') . "<br>";
    echo "✅ url('c=Auth&a=login'): " . url('c=Auth&a=login') . "<br>";
    
    // 4. Verificar conexión a base de datos
    echo "<h2>🗄️ BASE DE DATOS</h2>";
    try {
        require_once __DIR__ . '/app/config/database.php';
        $conn = Database::connect();
        echo "✅ Conexión exitosa<br>";
        
        // Verificar tabla usuarios
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Usuarios en BD: " . $result['total'] . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Error de BD: " . $e->getMessage() . "<br>";
        $errores++;
    }
}

echo "<hr>";
echo "<h2>📋 RESULTADO</h2>";
if ($errores === 0) {
    echo "<p style='color: green; font-size: 20px;'><strong>✅ SISTEMA FUNCIONANDO CORRECTAMENTE</strong></p>";
    echo "<h3>🚀 Acceder al sistema:</h3>";
    echo "<p><a href='" . FULL_URL . "' style='font-size: 18px; color: blue;'>" . FULL_URL . "</a></p>";
    echo "<h3>👤 Credenciales de prueba:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@empresa.com / 123456</li>";
    echo "<li><strong>Desarrollador:</strong> juan.perez@empresa.com / 123456</li>";
    echo "<li><strong>Gestor:</strong> maria.garcia@empresa.com / 123456</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red; font-size: 20px;'><strong>❌ ERRORES ENCONTRADOS: {$errores}</strong></p>";
    echo "<p>Corrige los errores antes de usar el sistema.</p>";
}

echo "<hr>";
echo "<p><em>Prueba ejecutada el " . date('Y-m-d H:i:s') . "</em></p>";
?>