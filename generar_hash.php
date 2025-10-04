<?php
/**
 * Script para generar hashes de contraseñas
 * Ejecutar en: http://localhost/proyect-prub/generar_hash.php
 */

// Contraseña que queremos hashear
$contrasena = "123456";

// Generar el hash usando password_hash() de PHP
$hash = password_hash($contrasena, PASSWORD_DEFAULT);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador de Hash</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #f1c40f;
            padding-bottom: 10px;
        }
        .info {
            background: #e8f5e9;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #4caf50;
        }
        .hash-box {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            word-break: break-all;
            font-family: monospace;
            font-size: 14px;
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
            margin-top: 15px;
        }
        .sql-code {
            background: #263238;
            color: #aed581;
            padding: 20px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .success {
            color: #4caf50;
            font-weight: bold;
        }
        .test {
            background: #fff3cd;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Generador de Hash de Contraseñas</h1>
        
        <div class="info">
            <strong>✅ Hash generado exitosamente!</strong>
        </div>

        <div class="label">Contraseña original:</div>
        <div class="hash-box"><?= htmlspecialchars($contrasena) ?></div>

        <div class="label">Hash generado:</div>
        <div class="hash-box"><?= htmlspecialchars($hash) ?></div>

        <div class="test">
            <strong>🧪 Verificación del hash:</strong><br>
            <?php if (password_verify($contrasena, $hash)): ?>
                <span class="success">✅ La verificación es correcta!</span>
            <?php else: ?>
                <span style="color: red;">❌ Error en la verificación</span>
            <?php endif; ?>
        </div>

        <h2>📋 SQL para actualizar la base de datos:</h2>
        <div class="sql-code">
-- Actualizar todos los usuarios con la nueva contraseña hasheada<br>
UPDATE usuarios SET contrasena = '<?= $hash ?>' WHERE id_usuario IN (1, 2, 3, 4);<br>
<br>
-- O insertar usuarios nuevos:<br>
INSERT INTO usuarios (nombre, correo, contrasena, id_rol, activo) VALUES<br>
&nbsp;&nbsp;('Admin Test', 'admin@test.com', '<?= $hash ?>', 1, TRUE),<br>
&nbsp;&nbsp;('Dev Test', 'dev@test.com', '<?= $hash ?>', 2, TRUE);
        </div>

        <h2>🔄 Generar otro hash</h2>
        <form method="POST">
            <input type="text" name="nueva_contrasena" placeholder="Ingresa una contraseña" style="padding: 10px; width: 300px; font-size: 14px;">
            <button type="submit" style="padding: 10px 20px; background: #f1c40f; border: none; cursor: pointer; font-weight: bold;">Generar Hash</button>
        </form>

        <?php if (isset($_POST['nueva_contrasena']) && !empty($_POST['nueva_contrasena'])): ?>
            <?php
            $nueva = $_POST['nueva_contrasena'];
            $nuevo_hash = password_hash($nueva, PASSWORD_DEFAULT);
            ?>
            <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196f3;">
                <strong>Nuevo hash generado:</strong><br>
                <div class="hash-box" style="margin-top: 10px;"><?= htmlspecialchars($nuevo_hash) ?></div>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding: 15px; background: #ffebee; border-left: 4px solid #f44336;">
            <strong>⚠️ IMPORTANTE:</strong><br>
            Este archivo es solo para desarrollo. Elimínalo después de obtener el hash.
        </div>
    </div>
</body>
</html>
