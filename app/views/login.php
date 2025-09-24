<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- Enlace al CSS -->
<<<<<<< Updated upstream
    <link rel="stylesheet" href="/Assets/Css/styles.css">
=======
    <link rel="stylesheet" href="Assets/Css/styles.css">
>>>>>>> Stashed changes
</head>
<body>
    <div class="login-page">
        <!-- Cabecera -->
        <div class="brand">
            <div class="brand-title">SOFTWARE DE GESTIÓN DE PROYECTOS</div>
            <div class="brand-subtitle">GRUPO VICENTE INVERSIONES E.I.R.L.</div>
        </div>

        <!-- Tarjeta del formulario -->
        <div class="card">
            <form method="POST" action="/index.php" class="form">
                <!-- Correo -->
                <div class="input-group">
                    <label class="input-label">Correo:</label>
                    <input type="email" name="email" class="input" placeholder="Ingrese su correo" required>
                    <div class="helper-text">Ingrese su correo</div>
                </div>

                <!-- Contraseña -->
                <div class="input-group">
                    <label class="input-label">Contraseña:</label>
                    <input type="password" name="password" class="input" placeholder="Ingrese su contraseña" required>
                    <div class="helper-text">Ingrese su contraseña</div>
                </div>

                <!-- Botón único de inicio de sesión -->
                <div class="btn-row">
                    <button type="submit" class="btn">Iniciar Sesión</button>
                </div>

                <!-- Mensaje de error -->
                <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
            </form>
        </div>

        <!-- Footer -->
        <div class="footer">
            © 2024 Grupo Vicente Inversiones E.I.R.L.
        </div>
    </div>
</body>
</html>
