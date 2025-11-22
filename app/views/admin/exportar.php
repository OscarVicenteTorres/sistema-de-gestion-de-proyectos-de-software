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
    <!-- Single CSS entrypoint -->
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
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
                    <li><a href="?c=Tarea&a=index"><i class="fa-regular fa-calendar-check"></i> Tareas</a></li>
                    <li><a href="?c=Proyecto&a=exportar" class="active"><i class="fa-solid fa-chart-line"></i> Exportar</a></li>
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
                <div class="col-md-4">
                    <div class="card border-warning border-3">
                        <h6 class="text-warning fw-bold">Totales</h6>
                        <h2 class="text-warning fw-bold mb-0" id="statProyectosRegistrados"><span><?= $estadisticas['total_registrados'] ?? 0 ?></span></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning border-3">
                        <h6 class="text-warning fw-bold">En Curso</h6>
                        <h2 class="text-warning fw-bold mb-0" id="statProyectosRegistrados"><span><?= $estadisticas['en_curso'] ?? 0 ?></span></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning border-3">
                        <h6 class="text-warning fw-bold">Completados</h6>
                        <h2 class="text-warning fw-bold mb-0" id="statProyectosRegistrados"><span><?= $estadisticas['completados'] ?? 0 ?></span></h2>
                    </div>
                </div>
            </div>

            <div class="container mt-4">

                <h2 class="fw-bold mb-4">Reporte de Proyectos</h2>

                <table class="table table-bordered table-hover" id="tablaExportar">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Herramienta</th>
                            <th>Avance</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaExportarBody">
                        <tr>
                            <td colspan="4" class="text-center py-3">Cargando datos...</td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <!-- BOTÓN QUE ABRE EL MODAL -->
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exportarModal">
                Exportar
            </button>

            <div class="modal fade" id="exportarModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <!-- FORMULARIO REAL -->
                        <form action="?c=Proyecto&a=exportarpdf" method="POST">

                            <!-- HEADER -->
                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title">Exportar Proyectos</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <!-- CUERPO -->
                            <div class="modal-body">
                                <?php foreach ($proyectos as $p): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="proyectos[]"
                                            value="<?= $p['id_proyecto'] ?>">
                                        <label class="form-check-label">
                                            <?= htmlspecialchars($p['nombre']) ?>
                                            <small class="text-muted">
                                                (<?= htmlspecialchars($p['estado']) ?>,
                                                <?= htmlspecialchars($p['porcentaje_avance'] ?? $p['avance'] ?? '0') ?>%)
                                            </small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                                <hr>

                                <label class="form-label fw-bold">Formato de exportación:</label>
                                <select class="form-select" name="formato">
                                    <option value="PDF">PDF</option>
                                    <option value="CSV">CSV</option>
                                </select>
                            </div>

                            <!-- FOOTER -->
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-download"></i> Exportar
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                            </div>

                        </form> <!-- FIN DEL FORM -->
                    </div>
                </div>
            </div>





            <script src="<?php echo asset('js/exportar.js'); ?>"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>