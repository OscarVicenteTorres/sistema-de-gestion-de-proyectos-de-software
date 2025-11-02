<?php
// Debug para verificar consulta de tareas
require_once 'app/config/database.php';

session_start();

// Simular usuario logueado - CAMBIA ESTE ID por el tuyo
$id_usuario_test = 1; // <-- CAMBIA ESTE NÚMERO

$conn = Database::connect();

// Consulta EXACTA que usa el modelo
$sql = "SELECT 
            t.id_tarea,
            t.id_usuario,
            t.titulo,
            t.descripcion,
            t.area_asignada,
            t.fecha_inicio,
            t.fecha_limite,
            t.estado,
            t.porcentaje_avance,
            t.fecha_creacion,
            u.nombre as usuario_nombre,
            u.apellido as usuario_apellido
        FROM tareas t
        LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
        WHERE t.id_proyecto IN (
            SELECT DISTINCT p.id_proyecto 
            FROM proyectos p
            INNER JOIN tareas t2 ON p.id_proyecto = t2.id_proyecto
            WHERE t2.id_usuario = :id_usuario
        )
        ORDER BY 
            CASE 
                WHEN t.estado = 'Completado' THEN 2
                ELSE 1
            END ASC,
            t.fecha_limite ASC,
            t.fecha_creacion DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_usuario', $id_usuario_test, PDO::PARAM_INT);
$stmt->execute();
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>DEBUG DE TAREAS</h2>";
echo "<p><strong>ID Usuario Test:</strong> $id_usuario_test</p>";
echo "<p><strong>Total Tareas Encontradas:</strong> " . count($tareas) . "</p>";
echo "<hr>";

if (count($tareas) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID Tarea</th>";
    echo "<th>ID Usuario</th>";
    echo "<th>Título</th>";
    echo "<th>Proyecto</th>";
    echo "<th>Porcentaje</th>";
    echo "<th>Estado</th>";
    echo "<th>Fecha Límite</th>";
    echo "</tr>";
    
    foreach ($tareas as $tarea) {
        // Obtener nombre del proyecto
        $sql_proyecto = "SELECT nombre FROM proyectos WHERE id_proyecto = (SELECT id_proyecto FROM tareas WHERE id_tarea = :id_tarea)";
        $stmt_p = $conn->prepare($sql_proyecto);
        $stmt_p->bindParam(':id_tarea', $tarea['id_tarea']);
        $stmt_p->execute();
        $proyecto = $stmt_p->fetch(PDO::FETCH_ASSOC);
        
        echo "<tr>";
        echo "<td>" . $tarea['id_tarea'] . "</td>";
        echo "<td style='background: " . ($tarea['id_usuario'] == $id_usuario_test ? '#90EE90' : '#FFB6C1') . ";'>" . $tarea['id_usuario'] . "</td>";
        echo "<td>" . htmlspecialchars($tarea['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars($proyecto['nombre'] ?? 'N/A') . "</td>";
        echo "<td>" . $tarea['porcentaje_avance'] . "%</td>";
        echo "<td>" . $tarea['estado'] . "</td>";
        echo "<td>" . $tarea['fecha_limite'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Tareas que deberían mostrarse (id_usuario = $id_usuario_test):</h3>";
    $tareas_usuario = array_filter($tareas, function($t) use ($id_usuario_test) {
        return $t['id_usuario'] == $id_usuario_test;
    });
    echo "<p><strong>Total:</strong> " . count($tareas_usuario) . "</p>";
    
} else {
    echo "<p style='color: red;'>❌ No se encontraron tareas para este usuario.</p>";
    echo "<p>Verifica:</p>";
    echo "<ul>";
    echo "<li>Que el id_usuario en la tabla 'tareas' sea correcto</li>";
    echo "<li>Que el id_proyecto en la tabla 'tareas' exista</li>";
    echo "<li>Que el usuario esté asignado a ese proyecto</li>";
    echo "</ul>";
}
?>
