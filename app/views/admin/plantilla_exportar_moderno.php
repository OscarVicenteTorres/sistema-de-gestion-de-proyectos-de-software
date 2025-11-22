<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte - <?= htmlspecialchars($proyecto['nombre_proyecto'] ?? 'Proyecto') ?></title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 18px; }
    h1 { font-size: 16px; margin-bottom: 6px; }
    h2 { font-size: 13px; background:#f0f4f8; padding:6px; margin-top:14px; border-left:4px solid #2b7cff; }
    table { width:100%; border-collapse:collapse; margin-top:8px; }
    th, td { border:1px solid #d6e2ef; padding:6px; font-size:11px; vertical-align:top; }
    th { background:#f7fbff; text-align:left; }
    .label { width:30%; font-weight:600; background:#fbfdff; }
    .badge { display:inline-block; padding:3px 6px; border-radius:4px; font-size:10px; color:#fff; }
    .badge-success { background:#28a745; }
    .badge-warning { background:#ffc107; color:#000; }
    .badge-danger { background:#dc3545; }
    .progress-cell { text-align:right; font-weight:600; }
    .small { font-size:10px; color:#666; }
</style>
</head>
<body>

<h1>Información General del Proyecto</h1>
<table>
    <tr><td class="label">Nombre del proyecto</td><td><?= htmlspecialchars($proyecto['nombre_proyecto'] ?? $proyecto['nombre'] ?? '') ?></td></tr>
    <tr><td class="label">Área / Departamento</td><td><?= htmlspecialchars($proyecto['area'] ?? $proyecto['categoria'] ?? '') ?></td></tr>
    <tr><td class="label">Fecha de inicio</td><td><?= htmlspecialchars($proyecto['fecha_inicio'] ?? '') ?></td></tr>
    <tr><td class="label">Fecha estimada de finalización</td><td><?= htmlspecialchars($proyecto['fecha_fin'] ?? '') ?></td></tr>
    <tr><td class="label">Duración estimada</td><td><?= htmlspecialchars($proyecto['duracion'] ?? '') ?></td></tr>
    <tr><td class="label">Descripción general</td><td><?= nl2br(htmlspecialchars($proyecto['descripcion'] ?? '')) ?></td></tr>
    <tr><td class="label">Equipo / Integrantes</td><td><?= nl2br(htmlspecialchars($proyecto['encargados'] ?? '')) ?></td></tr>
    <tr><td class="label">Recursos</td><td><?= nl2br(htmlspecialchars($proyecto['recursos'] ?? '')) ?></td></tr>
    <tr><td class="label">Herramientas</td><td><?= nl2br(htmlspecialchars($proyecto['tecnologias'] ?? '')) ?></td></tr>
    <tr><td class="label">Alcance</td><td><?= nl2br(htmlspecialchars($proyecto['cliente'] ?? '')) ?></td></tr>
</table>

<h2>Estado del Proyecto y Avances Generales</h2>
<table>
    <tr>
        <td class="label">Porcentaje global de avance</td>
        <td class="progress-cell"><?= htmlspecialchars($proyecto['progreso'] ?? 0) ?>%
            <?php
                $p = (int)($proyecto['progreso'] ?? 0);
                if ($p >= 100) $cls = 'badge-success';
                elseif ($p >= 50) $cls = 'badge-warning';
                else $cls = 'badge-danger';
            ?>
            <span class="badge <?= $cls ?>"><?= $p ?>%</span>
        </td>
    </tr>
    <tr>
        <td class="label">Hitos alcanzados</td>
        <td class="small"><?= htmlspecialchars(implode(', ', array_map(function($h){ return $h['nombre'] ?? ''; }, $hitos ?: [])) ?: '—') ?></td>
    </tr>
</table>

<h2>Cronograma (fechas clave)</h2>
<table>
    <thead><tr><th>Hito</th><th>Fecha</th><th>Descripción</th></tr></thead>
    <tbody>
        <?php if (!empty($hitos)): foreach ($hitos as $h): ?>
            <tr>
                <td><?= htmlspecialchars($h['nombre'] ?? '') ?></td>
                <td><?= htmlspecialchars($h['fecha'] ?? '') ?></td>
                <td><?= htmlspecialchars($h['descripcion'] ?? '') ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="3" align="center">Sin hitos registrados</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<h2>Detalle de Tareas y Avances</h2>
<table>
    <thead>
        <tr>
            <th>Tarea</th><th>Descripción</th><th>Responsable(s)</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Progreso</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($tareas)): foreach ($tareas as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t['nombre_tarea'] ?? $t['nombre'] ?? '') ?></td>
            <td><?= htmlspecialchars($t['descripcion'] ?? '') ?></td>
            <td><?= htmlspecialchars($t['responsables'] ?? $t['responsable'] ?? '') ?></td>
            <td><?= htmlspecialchars($t['fecha_inicio'] ?? '') ?></td>
            <td><?= htmlspecialchars($t['fecha_fin'] ?? '') ?></td>
            <td><?= htmlspecialchars($t['estado'] ?? '') ?></td>
            <td><?= htmlspecialchars($t['progreso'] ?? 0) ?>%</td>
        </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="7" align="center">Sin tareas registradas</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
