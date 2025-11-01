<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="<?php echo asset('css/inicio/login.css'); ?>">
</head>

<body>

    <div class="gradient-layer"></div>
    <div class="grid-overlay"></div>

    <div class="page-container">
        <section class="login-section">
            <div class="login-card">
                <div class="logo-area">
                    <img src="<?php echo asset('img/logoemp.png'); ?>" alt="GRUVITEC" class="logo-neon">
                    <h1>SOFTWARE DE GESTIÓN DE PROYECTOS</h1>
                    <p>Grupo Vicente Inversiones E.I.R.L.</p>
                </div>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" id="correo" name="correo" placeholder="correo@gmail.com" required>
                    </div>

                    <div class="form-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" placeholder="********" required>
                    </div>

                    <button type="submit" class="btn-login">Iniciar Sesión</button>

                    <?php if (isset($error)): ?>
                        <p class="error"><?= $error ?></p>
                    <?php endif; ?>
                </form>

                <footer>
                    © 2025 Grupo Vicente Inversiones E.I.R.L. - Todos los derechos reservados
                </footer>
            </div>
        </section>
    </div>

    <div id="transition-screen" class="transition-screen">
        <img src="<?php echo asset('img/logoemp.png'); ?>" alt="GRUVITEC" class="logo-neon">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            const loginSection = document.querySelector('.login-section');
            const transitionScreen = document.getElementById('transition-screen');

            form.addEventListener('submit', (e) => {
                e.preventDefault();

                loginSection.classList.add('fade-out');
                setTimeout(() => transitionScreen.classList.add('active'), 500);

                // Enviar el formulario real luego de la animación
                setTimeout(() => {
                    form.submit(); 
                }, 2000);
            });
        });
    </script>


</body>

</html>