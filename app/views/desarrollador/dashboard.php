<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Desarrollador</title>
</head>
<body>
    <!-- views/desarrollador/dashboard.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Desarrollador</title>
    <link rel="stylesheet" href="<?php echo asset('css/admin/base.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/desarrollador/dashboard-desarrollador.css'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container py-4">
    <h2 class="mb-4 fw-bold text-primary">👷‍♂️ Panel del Desarrollador</h2>

    <?php if (!empty($proyectos)) : ?>
        <?php foreach ($proyectos as $proyecto) : ?>
            <div class="project-card">
                <div class="project-header">
                    <div>
                        <h5 class="project-title"><?= htmlspecialchars($proyecto['nombre']) ?></h5>
                        <p class="text-muted mb-1"><?= htmlspecialchars($proyecto['descripcion']) ?></p>
                    </div>
                    <span class="badge bg-info text-dark"><?= htmlspecialchars($proyecto['area']) ?></span>
                </div>

                <div class="mt-2">
                    <div class="progress mb-2">
                        <div class="progress-bar 
                            <?php
                                if ($proyecto['estado'] == 'Completado') echo 'bg-success';
                                elseif ($proyecto['estado'] == 'En progreso') echo 'bg-primary';
                                elseif ($proyecto['estado'] == 'Pendiente') echo 'bg-warning';
                                else echo 'bg-danger';
                            ?>"
                            style="width: <?= intval($proyecto['porcentaje_avance']) ?>%;">
                        </div>
                    </div>
                    <p class="mb-1"><strong>Progreso:</strong> <?= intval($proyecto['porcentaje_avance']) ?>%</p>
                </div>

                <div class="mt-3">
                    <h6 class="fw-semibold">Tareas</h6>
                    <?php if (!empty($proyecto['tareas'])) : ?>
                        <?php foreach ($proyecto['tareas'] as $tarea) : ?>
                            <div class="tarea">
                                <div class="d-flex justify-content-between">
                                    <span><?= htmlspecialchars($tarea['titulo']) ?></span>
                                    <span class="estado 
                                        <?= strtolower(str_replace(' ', '-', $tarea['estado'])) ?>">
                                        <?= htmlspecialchars($tarea['estado']) ?>
                                    </span>
                                </div>
                                <small>Inicio: <?= htmlspecialchars($tarea['fecha_inicio']) ?> |
                                       Límite: <?= htmlspecialchars($tarea['fecha_limite']) ?></small>
                                <div class="progress mt-2">
                                    <div class="progress-bar 
                                        <?= $tarea['estado'] == 'Completado' ? 'bg-success' : 'bg-primary' ?>"
                                        style="width: <?= intval($tarea['porcentaje_avance']) ?>%;">
                                    </div>
                                </div>
                                <small>Avance: <?= intval($tarea['porcentaje_avance']) ?>%</small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No hay tareas asignadas a este proyecto.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No tienes proyectos asignados aún.</div>
    <?php endif; ?>
</div>

</body>
</html>

    
    
</html>
