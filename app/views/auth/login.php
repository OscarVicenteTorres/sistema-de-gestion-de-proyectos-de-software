<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <!-- Enlace al CSS de inicio -->
    <link rel="stylesheet" href="<?php echo asset('css/inicio/login.css'); ?>">
</head>
<body>
    <h1>SOFTWARE DE GESTION DE PROYECTOS</h1>
    <div class="subtitulo">GRUPO VICENTE INVERSIONES E.I.R.L.</div>

    <div class="login-box">
        <form method="POST" action="">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" placeholder="Ingrese su correo" required>

            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required>

            <button type="submit">Iniciar Sesión</button>
        </form>

        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
    </div>

    <footer>
        © 2025 Grupo Vicente Inversiones E.I.R.L.
    </footer>
</body>
</html>
