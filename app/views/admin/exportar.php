<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas - Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/admin/base.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/admin/menu.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/admin/Exportar.css'); ?>">
</head>
<body>
    <div class="dashboard-container">
        <!-- Barra Lateral de Navegación -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>Grupo Vicente</h1>
                <h2>Inversiones E.I.R.L.</h2>
                <p>Gestor de Proyectos</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="?c=Usuario&a=dashboardAdmin"><i class="fa-regular fa-folder"></i> Proyectos</a></li>
                    <li><a href="?c=Tarea&a=index" class="active"><i class="fa-regular fa-calendar-check"></i> Tareas</a></li>
                    <li><a href="?c=Proyecto&a=exportar"><i class="fa-solid fa-chart-line"></i> Exportar</a></li>
                    <li><a href="?c=Usuario&a=index"><i class="fa-regular fa-user"></i> Cuentas</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <p class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Administrador') ?></p>
                    <p class="user-role">Administrador</p>
                </div>
                <a href="?c=Auth&a=logout" class="logout-icon" title="Cerrar sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </aside>

        <!-- Modal Exportar -->
        <div class="exportar-container">

            <h2>Resumen de Proyectos</h2>
            <div class="stats">
                <div class="card total">Totales: <span><?= $estadisticas['total'] ?? 0 ?></span></div>
                <div class="card activos">Activos: <span><?= $estadisticas['activos'] ?? 0 ?></span></div>
                <div class="card completados">Completados: <span><?= $estadisticas['completados'] ?? 0 ?></span></div>
            </div>

            <h3>Listado de Proyectos</h3>
            <table class="tabla-proyectos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Herramienta</th>
                        <th>Avance</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($proyectos)): ?>
                        <?php foreach ($proyectos as $index => $p): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                <td><?= htmlspecialchars($p['categoria'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['porcentaje_avance'] ?? 0) ?>%</td>
                                <td><?= htmlspecialchars($p['estado']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No hay proyectos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <button id="btnExportar" class="btn-exportar">Exportar</button>

            <!-- Popup Modal -->
            <div id="modalExportar" class="modal">
                <div class="modal-content">
                    <span id="cerrarModal" class="cerrar">&times;</span>
                    <h3>Seleccionar proyectos a exportar</h3>
                    <form method="POST" action="exportar.php">
                        <?php foreach ($proyectos as $p): ?>
                            <label>
                                <input type="checkbox" name="proyectos[]" value="<?= $p['id_proyecto'] ?>">
                                <?= htmlspecialchars($p['nombre']) ?>
                            </label><br>
                        <?php endforeach; ?>

                        <div class="formato">
                            <label>Formato:</label>
                            <select name="formato">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-confirmar">Exportar</button>
                    </form>
                </div>
            </div>
        </div>





        <script src="<?php echo asset('js/exportar.js'); ?>"></script>
</body>
</html>
