<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencia</title>
    <!-- Single CSS entrypoint -->
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>

<body>

    <div class="container-asistencia">
        <h1>Registro de Asistencia</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?> 👋</p>

        <div class="btns-asistencia">
            <a href="?c=Usuario&a=dashboard" class="btn-asistencia" style="display: inline-block; text-decoration: none; text-align: center; padding: 14px 45px; background: #1E2746; color: var(--color-primary); font-weight: 600; border: none; border-radius: 15px; cursor: pointer;">
                <i class="fas fa-arrow-right"></i> Ir al Dashboard
            </a>
            <form method="POST" action="?c=Asistencia&a=registrarEntrada">
                <button type="submit" class="btn-asistencia btn-entrada">Registrar Entrada</button>
            </form>
            <form method="POST" action="?c=Asistencia&a=registrarSalida">
                <button type="submit" class="btn-asistencia btn-salida">Registrar Salida</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora de Entrada</th>
                        <th>Hora de Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['fecha']); ?></td>
                            <td><?= htmlspecialchars($fila['hora_entrada'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($fila['hora_salida'] ?? '-'); ?></td>
                            <td>
                                <span class="estado <?= strtolower($fila['estado']); ?>">
                                    <?= htmlspecialchars($fila['estado']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: '<?php echo $_SESSION['mensaje_error']; ?>',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#FFD700',
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        </script>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>




</body>

</html>