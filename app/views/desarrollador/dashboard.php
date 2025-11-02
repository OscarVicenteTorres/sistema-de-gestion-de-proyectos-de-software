<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Desarrollador</title>
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
    <style>
        /* Panel lateral de notificaciones (estilo imagen 2) */
        .notifications-sidebar {
            position: fixed;
            top: 0;
            right: -450px;
            width: 450px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 15px rgba(0,0,0,0.3);
            transition: right 0.3s ease;
            z-index: 1046;
            overflow-y: auto;
        }
        .notifications-sidebar.show {
            right: 0;
        }
        .notification-item {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
        }
        .notification-item:hover {
            background: #f9fafb;
        }
        .notification-badge-modificada {
            background: #3B82F6;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
        }
        .notification-badge-vencido {
            background: #EF4444;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
        }
        .notification-badge-por-vencer {
            background: #FBBF24;
            color: #1F2937;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
        }
        .overlay-notifications {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1045;
            pointer-events: auto;
        }
        .overlay-notifications.show {
            display: block;
        }
        
        /* Estilos para las tarjetas de proyecto y tareas */
        .proyecto-card {
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .proyecto-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .tarea-card {
            transition: all 0.2s ease;
            border-left: 4px solid #e5e7eb;
        }
        
        .tarea-card:hover {
            border-left-color: #FFD700;
            background-color: #fff !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .projects-table-container {
            background-color: transparent;
        }
    </style>
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
                    <li><a href="?c=Usuario&a=dashboard" class="active"><i class="fa-regular fa-folder"></i> Mis Proyectos</a></li>
                    <li><a href="?c=Asistencia&a=index"><i class="fa-regular fa-calendar"></i> Asistencia</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <p class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Desarrollador') ?></p>
                    <p class="user-role">Desarrollador</p>
                </div>
                <a href="?c=Auth&a=logout" class="logout-icon" title="Cerrar sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h1 class="h3 mb-1 fw-bold" style="color: #FFD700;">Desarrollador Software</h1>
                    <p class="text-muted mb-0">Bienvenido <?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Desarrollador') ?></p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button id="btnTareasCompletadas" class="btn btn-success text-white fw-bold shadow-sm">
                        <i class="fa-solid fa-check-circle me-2"></i>Tareas Completadas
                    </button>
                    <button id="btnNotificaciones" class="btn btn-outline-dark" onclick="toggleNotifications()">
                        <i class="fa-solid fa-bell fs-5"></i>
                    </button>
                </div>
            </header>

            <section class="projects-section">
                <h1 class="section-title mb-4 fw-bold">Mis Proyectos y Tareas</h1>

                <!-- DEBUG: Mostrar datos -->
                <?php if (isset($_GET['debug'])) : ?>
                    <div class="alert alert-warning">
                        <h5>DEBUG INFO:</h5>
                        <p><strong>ID Usuario Logueado:</strong> <?= $_SESSION['usuario']['id_usuario'] ?></p>
                        <p><strong>Total Proyectos:</strong> <?= count($proyectos) ?></p>
                        <?php foreach ($proyectos as $idx => $proy) : ?>
                            <hr>
                            <p><strong>Proyecto <?= $idx + 1 ?>:</strong> <?= $proy['nombre'] ?></p>
                            <p><strong>Total Tareas:</strong> <?= count($proy['tareas']) ?></p>
                            <?php foreach ($proy['tareas'] as $t) : ?>
                                <p class="ms-3">
                                    - Tarea: <?= $t['titulo'] ?> | 
                                    ID Usuario: <?= $t['id_usuario'] ?? 'NULL' ?> | 
                                    Porcentaje: <?= $t['porcentaje_avance'] ?>%
                                </p>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($proyectos)) : ?>
                    <div class="projects-table-container">
                        <?php foreach ($proyectos as $proyecto) : ?>
                            <!-- Tarjeta de Proyecto -->
                            <div class="proyecto-card mb-4 p-4 bg-white rounded shadow-sm" data-tareas='<?= json_encode($proyecto['tareas']) ?>'>
                                <!-- Encabezado del Proyecto -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fa-solid fa-folder" style="color: var(--dorado); font-size: 1.2rem;"></i>
                                            <h4 class="fw-bold mb-0" style="color: var(--dorado);"><?= htmlspecialchars($proyecto['nombre']) ?></h4>
                                        </div>
                                        <p class="text-muted small mb-1">
                                            <strong>Área:</strong> <?= htmlspecialchars($proyecto['categoria']) ?>
                                        </p>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($proyecto['descripcion'] ?? 'Sin descripción') ?></p>
                                    </div>
                                    <div class="text-end ms-3">
                                        <span class="badge fs-6 px-3 py-2 <?php
                                            switch ($proyecto['estado']) {
                                                case 'Completado':
                                                    echo 'bg-success';
                                                    break;
                                                case 'En Desarrollo':
                                                case 'Activo':
                                                    echo 'bg-primary';
                                                    break;
                                                default:
                                                    echo 'bg-warning text-dark';
                                            }
                                        ?>"><?= htmlspecialchars($proyecto['estado']) ?></span>
                                        <div class="mt-2">
                                            <i class="fa-regular fa-calendar text-muted"></i>
                                            <span class="text-muted small d-block">Tiempo Límite</span>
                                            <strong class="d-block"><?= date('d/m/Y', strtotime($proyecto['fecha_fin'])) ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Barra de Progreso del Proyecto -->
                                <div class="mb-3">
                                    <div class="progress" style="height: 30px; border-radius: 10px; background-color: #e9ecef;">
                                        <div class="progress-bar" style="width: <?= intval($proyecto['porcentaje_avance']) ?>%; background-color: #1e293b; font-weight: bold; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                            <?= intval($proyecto['porcentaje_avance']) ?>%
                                        </div>
                                    </div>
                                </div>

                                <!-- Tareas del Proyecto -->
                                <?php if (!empty($proyecto['tareas'])) : ?>
                                    <?php foreach ($proyecto['tareas'] as $tarea) : ?>
                                        <!-- VALIDACIÓN: Solo mostrar tareas del usuario logueado -->
                                        <?php if (isset($tarea['id_usuario']) && $tarea['id_usuario'] == $_SESSION['usuario']['id_usuario']) : ?>
                                            <div class="tarea-card mb-3 p-3 bg-white rounded border" data-tarea-id="<?= $tarea['id_tarea'] ?>" style="border-left: 4px solid #e5e7eb;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <!-- Título de la Tarea -->
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1" style="color: #1F2937;"><?= htmlspecialchars($tarea['titulo']) ?></h6>
                                                        <small class="text-muted">
                                                            <i class="fa-regular fa-calendar me-1"></i>
                                                            Fecha límite: <?= date('d/m/Y', strtotime($tarea['fecha_limite'])) ?>
                                                        </small>
                                                    </div>

                                                    <!-- Estado Badge -->
                                                    <span class="badge px-3 py-2 <?php
                                                        if ($tarea['porcentaje_avance'] == 0) {
                                                            echo 'bg-warning text-dark';
                                                        } elseif ($tarea['porcentaje_avance'] >= 100) {
                                                            echo 'bg-success';
                                                        } else {
                                                            echo 'bg-info';
                                                        }
                                                    ?>">
                                                        <?php
                                                        if ($tarea['porcentaje_avance'] == 0) {
                                                            echo 'Pendiente';
                                                        } elseif ($tarea['porcentaje_avance'] >= 100) {
                                                            echo 'Completado';
                                                        } else {
                                                            echo 'En Progreso';
                                                        }
                                                        ?>
                                                    </span>
                                                </div>

                                                <!-- Barra de Progreso de la Tarea -->
                                                <div class="mb-2">
                                                    <div class="progress" style="height: 20px; border-radius: 10px; background-color: #e9ecef;">
                                                        <div class="progress-bar" style="width: <?= intval($tarea['porcentaje_avance']) ?>%; background: linear-gradient(to right, #3B82F6, #60a5fa); font-weight: bold; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                            <?= intval($tarea['porcentaje_avance']) ?>%
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Botones de Acción -->
                                                <div class="d-flex justify-content-end gap-2 mt-2">
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="verDetallesTarea(<?= $tarea['id_tarea'] ?>, '<?= htmlspecialchars($tarea['titulo'], ENT_QUOTES) ?>')" title="Ver detalles">
                                                        <i class="fa-solid fa-eye me-1"></i> Ver
                                                    </button>
                                                    <button class="btn btn-sm btn-success" onclick="abrirModalAvance(<?= $tarea['id_tarea'] ?>, '<?= htmlspecialchars($tarea['titulo'], ENT_QUOTES) ?>', <?= $tarea['porcentaje_avance'] ?>)" title="Reportar avance">
                                                        <i class="fa-solid fa-check me-1"></i> Reportar
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="alert alert-info mb-3">
                                        <i class="fa-solid fa-info-circle me-2"></i>No hay tareas asignadas a este proyecto.
                                    </div>
                                <?php endif; ?>
                            </div> <!-- Fin proyecto-card -->

                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="alert alert-info shadow-sm p-4">
                        <i class="fa-solid fa-inbox me-2"></i>
                        <strong>No tienes proyectos asignados aún.</strong> Contacta al administrador para recibir tareas.
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <!-- ================================
        MODAL OVERLAY (único para todos los modales)
        ================================ -->
    <div class="modal-overlay" id="modal-overlay"></div>

    <!-- ================================
        PANEL LATERAL DE NOTIFICACIONES
        ================================ -->
    <div class="overlay-notifications" id="overlayNotifications" onclick="toggleNotifications()"></div>
    <div class="notifications-sidebar" id="notificationsSidebar">
        <div class="p-4 bg-white border-bottom">
            <h4 class="mb-0 fw-bold">Notificaciones</h4>
        </div>
        <div id="notificationsContent">
            <!-- Notificación 1: Modificada -->
            <div class="notification-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="fw-normal">La tarea "implementar Dashboard de Ventas" fue modificada</span>
                    <span class="notification-badge-modificada">Modificada</span>
                </div>
            </div>
            <!-- Notificación 2: Vencido -->
            <div class="notification-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="fw-normal">La tarea "integrar API de reportes" esta vencida</span>
                    <span class="notification-badge-vencido">Vencido</span>
                </div>
            </div>
            <!-- Notificación 3: Por Vencer -->
            <div class="notification-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="fw-normal">La tarea "implementar Dashboard de Ventas" está por vencer</span>
                    <span class="notification-badge-por-vencer">Por Vencer</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================
        MODAL: DETALLES DE LA TAREA (👁️)
        ================================ -->
    <div class="modal" id="modalDetallesTarea">
        <div class="modal-header">
            <h2 id="modalDetallesTareaTitle"><i class="fa-solid fa-info-circle me-2"></i>Detalles de la Tarea</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div id="contenidoDetallesTarea">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================
        MODAL: FASES DE LA TAREA
        ================================ -->
    <div class="modal modal-fases" id="modalFasesTarea">
        <div class="modal-header" style="background: linear-gradient(135deg, #1F2937 0%, #374151 100%);">
            <h2 style="color: white;"><i class="fa-solid fa-list-check me-2" style="color: #FFD700;"></i>Fases del Proyecto</h2>
            <button class="close-modal-btn" style="color: white;">&times;</button>
        </div>
        <div class="modal-body">
            <p class="text-muted mb-4 text-center">Selecciona la fase en la que te encuentras actualmente</p>
            
            <div class="d-grid gap-3">
                        <!-- Fase 1: Pendiente -->
                        <div class="fase-item p-3 rounded border" style="background-color: white; cursor: pointer; transition: all 0.3s;" 
                             onclick="seleccionarFase(0, 'Pendiente')"
                             onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                             onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-hourglass-start me-3" style="font-size: 1.5rem; color: #6B7280;"></i>
                                    <div>
                                        <div class="fw-bold">Pendiente</div>
                                        <small class="text-muted">0% Completado</small>
                                    </div>
                                </div>
                                <span class="badge" style="background-color: #FBBF24; color: #1F2937;">Terminado</span>
                            </div>
                        </div>

                        <!-- Fase 2: Investigación -->
                        <div class="fase-item p-3 rounded border" style="background-color: white; cursor: pointer; transition: all 0.3s;" 
                             onclick="seleccionarFase(15, 'Investigación')"
                             onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                             onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-search me-3" style="font-size: 1.5rem; color: #3B82F6;"></i>
                                    <div>
                                        <div class="fw-bold">Investigación</div>
                                        <small class="text-muted">15% Completado</small>
                                    </div>
                                </div>
                                <span class="badge" style="background-color: #FBBF24; color: #1F2937;">Terminado</span>
                            </div>
                        </div>

                        <!-- Fase 3: Diseño -->
                        <div class="fase-item p-3 rounded border" style="background-color: white; cursor: pointer; transition: all 0.3s;" 
                             onclick="seleccionarFase(30, 'Diseño')"
                             onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                             onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-pen-ruler me-3" style="font-size: 1.5rem; color: #8B5CF6;"></i>
                                    <div>
                                        <div class="fw-bold">Diseño</div>
                                        <small class="text-muted">30% Completado</small>
                                    </div>
                                </div>
                                <span class="badge" style="background-color: #FBBF24; color: #1F2937;">Terminado</span>
                            </div>
                        </div>

                        <!-- Fase 4: En Desarrollo -->
                        <div class="fase-item p-3 rounded border" style="background-color: white; cursor: pointer; transition: all 0.3s;" 
                             onclick="seleccionarFase(60, 'En Desarrollo')"
                             onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                             onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-code me-3" style="font-size: 1.5rem; color: #10B981;"></i>
                                    <div>
                                        <div class="fw-bold">En Desarrollo</div>
                                        <small class="text-muted">60% Completado</small>
                                    </div>
                                </div>
                                <span class="badge" style="background-color: #FBBF24; color: #1F2937;">Terminado</span>
                            </div>
                        </div>

                        <!-- Fase 5: Implementación -->
                        <div class="fase-item p-3 rounded border" style="background-color: white; cursor: pointer; transition: all 0.3s;" 
                             onclick="seleccionarFase(85, 'Implementación')"
                             onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                             onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-rocket me-3" style="font-size: 1.5rem; color: #F59E0B;"></i>
                                    <div>
                                        <div class="fw-bold">Implementación</div>
                                        <small class="text-muted">85% Completado</small>
                                    </div>
                                </div>
                                <span class="badge" style="background-color: #FBBF24; color: #1F2937;">Terminado</span>
                            </div>
                        </div>

                        <!-- Fase 6: Completado -->
                        <div class="fase-item p-3 rounded border" style="background-color: white; cursor: pointer; transition: all 0.3s;" 
                             onclick="seleccionarFase(100, 'Completado')"
                             onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                             onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-check-circle me-3" style="font-size: 1.5rem; color: #10B981;"></i>
                                    <div>
                                        <div class="fw-bold">Completado</div>
                                        <small class="text-muted">100% Completado</small>
                                    </div>
                                </div>
                                <span class="badge" style="background-color: #FBBF24; color: #1F2937;">Terminado</span>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <!-- ================================
        MODAL: REPORTAR AVANCE (Formulario)
        ================================ -->
    <div class="modal" id="modalAvanceTarea">
        <div class="modal-header">
            <h2><i class="fa-solid fa-chart-line me-2"></i>Reportar Avance</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
                    <form id="formAvanceTarea">
                        <input type="hidden" id="inputIdTarea" name="id_tarea">

                        <!-- Nombre de la Tarea -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fa-solid fa-tasks me-1" style="color: var(--dorado);"></i>Tarea: <span id="nombreTareaForm" class="text-primary"></span>
                            </label>
                        </div>

                        <!-- Porcentaje de Avance -->
                        <div class="mb-4">
                            <label for="inputPorcentaje" class="form-label fw-semibold">
                                <i class="fa-solid fa-percent me-1" style="color: var(--dorado);"></i>Porcentaje de Avance
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="range" class="form-range" id="inputPorcentaje" min="0" max="100" value="0" style="flex-grow: 1;">
                                <span class="badge bg-warning text-dark fw-semibold" id="badgePorcentaje" style="min-width: 50px; text-align: center;">0%</span>
                            </div>
                            <small class="text-muted d-block mt-2">Desliza para seleccionar el porcentaje de avance</small>
                        </div>

                        <hr>

                        <!-- Checkbox: No pude completar -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="checkNoPudeCompletar" style="width: 1.25em; height: 1.25em;">
                            <label class="form-check-label ms-2" for="checkNoPudeCompletar">
                                <strong>No pude completar la tarea a tiempo</strong>
                            </label>
                        </div>

                        <!-- Campo: Nueva Fecha de Entrega (aparece si marca checkbox) -->
                        <div id="seccionNuevaFecha" style="display: none;" class="mb-4 p-3 bg-warning bg-opacity-10 rounded border border-warning border-opacity-50">
                            <label for="inputNuevaFecha" class="form-label fw-semibold">
                                <i class="fa-solid fa-calendar-check me-1" style="color: var(--rojo-vencido);"></i>Nueva Fecha de Entrega
                            </label>
                            <input type="date" class="form-control" id="inputNuevaFecha" name="nueva_fecha_limite">
                            <small class="text-muted d-block mt-2">Propón una nueva fecha para completar la tarea (esto notificará al administrador)</small>
                        </div>

                        <!-- Nota de Avances -->
                        <div class="mb-3">
                            <label for="textareaNota" class="form-label fw-semibold">
                                <i class="fa-solid fa-message me-1" style="color: var(--azul-progreso);"></i>Nota de Avances (Opcional)
                            </label>
                            <textarea class="form-control" id="textareaNota" name="nota_desarrollador" rows="4" placeholder="Describe los avances realizados, problemas encontrados, o cualquier comentario importante..."></textarea>
                            <small class="text-muted">Máximo 500 caracteres</small>
                        </div>
                    </form>
                </div>
                <div class="modal-actions" style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn btn-secondary close-modal-btn">Cancelar</button>
                    <button type="button" class="btn btn-warning text-dark fw-bold" onclick="enviarAvance()">
                        <i class="fa-solid fa-send me-1"></i>Enviar Avance
                    </button>
                </div>
        </div>
    </div>

    <!-- ================================
        MODAL: TAREAS COMPLETADAS
        ================================ -->
    <div class="modal" id="modalTareasCompletadas">
        <div class="modal-header" style="background-color: #10B981; color: white;">
            <h2><i class="fa-solid fa-circle-check me-2"></i>Tareas Completadas</h2>
            <button class="close-modal-btn" style="color: white;">&times;</button>
        </div>
        <div class="modal-body" id="contenidoTareasCompletadas">
            <div class="text-center py-5">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (solo para componentes básicos) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dashboard Desarrollador JS -->
    <script src="<?php echo asset('js/dashboard-desarrollador.js'); ?>"></script>
</body>

</html>