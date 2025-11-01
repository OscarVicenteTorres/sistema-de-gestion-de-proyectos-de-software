<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Desarrollador</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= asset('css/admin/base.css'); ?>">
    <link rel="stylesheet" href="<?= asset('css/desarrollador/Dash-Desarrollador.css'); ?>">
</head>

<body class="bg-light">

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary mb-0">👷‍♂️ Panel del Desarrollador</h2>

            <!-- 🔔 Botón Notificaciones -->
            <button id="btnNotificaciones" class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#modalNotificaciones">
                <i class="bi bi-bell fs-5"></i>
                <?php if (!empty($notificaciones) && count($notificaciones) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= count($notificaciones) ?>
                    </span>
                <?php endif; ?>
            </button>
        </div>

        <div class="modal fade" id="modalNotificaciones" tabindex="-1" aria-labelledby="modalNotificacionesLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalNotificacionesLabel">
                            <i class="bi bi-bell-fill me-2"></i>Notificaciones
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <?php if (!empty($notificaciones)) : ?>
                            <?php foreach ($notificaciones as $notif) : ?>
                                <div class="alert alert-light border-start border-4 mb-2 
                    <?= $notif['tipo'] == 'alerta' ? 'border-danger' : ($notif['tipo'] == 'info' ? 'border-primary' : 'border-success') ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= htmlspecialchars($notif['titulo']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($notif['mensaje']) ?></small>
                                        </div>
                                        <span class="text-muted small"><?= htmlspecialchars($notif['fecha']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No tienes notificaciones nuevas.</div>
                        <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($proyectos)) : ?>
            <?php foreach ($proyectos as $proyecto) : ?>
                <div class="card shadow-sm mb-4 border-0 project-card">
                    <div class="card-body">
                        <!-- Cabecera del Proyecto -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($proyecto['nombre']) ?></h5>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($proyecto['descripcion']) ?></p>
                            </div>
                            <span class="badge 
                            <?php
                            switch ($proyecto['estado']) {
                                case 'Completado':
                                    echo 'bg-success';
                                    break;
                                case 'En progreso':
                                    echo 'bg-primary';
                                    break;
                                case 'Pendiente':
                                    echo 'bg-warning text-dark';
                                    break;
                                default:
                                    echo 'bg-danger';
                            }
                            ?>">
                                <?= htmlspecialchars($proyecto['estado']) ?>
                            </span>
                        </div>

                        <!-- Progreso del Proyecto -->
                        <div class="mb-3">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: <?= intval($proyecto['porcentaje_avance']) ?>%;"
                                    aria-valuenow="<?= intval($proyecto['porcentaje_avance']) ?>"
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">Progreso: <?= intval($proyecto['porcentaje_avance']) ?>%</small>
                        </div>

                        <!-- Tareas -->
                        <h6 class="fw-semibold mt-3 mb-2 text-secondary">Tareas del Proyecto</h6>
                        <?php if (!empty($proyecto['tareas'])) : ?>
                            <?php foreach ($proyecto['tareas'] as $tarea) : ?>
                                <div class="border rounded p-2 mb-2 bg-white hover-shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($tarea['titulo']) ?></strong>
                                        <span class="badge 
                                        <?php
                                        switch ($tarea['estado']) {
                                            case 'Completado':
                                                echo 'bg-success';
                                                break;
                                            case 'Pendiente':
                                                echo 'bg-warning text-dark';
                                                break;
                                            case 'Retrasado':
                                                echo 'bg-danger';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                        }
                                        ?>">
                                            <?= htmlspecialchars($tarea['estado']) ?>
                                        </span>
                                    </div>

                                    <small class="text-muted">
                                        <i class="bi bi-calendar-event"></i>
                                        <?= htmlspecialchars($tarea['fecha_inicio']) ?> →
                                        <?= htmlspecialchars($tarea['fecha_limite']) ?>
                                    </small>

                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar 
                                        <?= $tarea['estado'] == 'Completado' ? 'bg-success' : 'bg-primary' ?>"
                                            style="width: <?= intval($tarea['porcentaje_avance']) ?>%; transition: width 0.6s;">
                                        </div>
                                    </div>
                                    <small class="text-muted">Avance: <?= intval($tarea['porcentaje_avance']) ?>%</small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No hay tareas asignadas a este proyecto.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info shadow-sm">No tienes proyectos asignados aún.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>