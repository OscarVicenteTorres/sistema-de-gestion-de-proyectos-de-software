<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Proyectos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/dashboard-admin.css'); ?>">
    <style>
        /* Forzar estilos del modal por encima de Bootstrap */
        .modal-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            z-index: 9998 !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transition: opacity 0.3s ease, visibility 0.3s ease !important;
            display: block !important;
        }
        
        .modal-overlay.active {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .modal {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) scale(0.8) !important;
            background-color: #ffffff !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
            z-index: 9999 !important;
            width: 90% !important;
            max-width: 680px !important;
            max-height: 90vh !important;
            overflow-y: auto !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transition: all 0.3s ease !important;
            display: block !important;
        }
        
        .modal.active {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translate(-50%, -50%) scale(1) !important;
        }
        
        /* Quitar resaltado azul de inputs */
        .modal-form input:focus,
        .modal-form textarea:focus,
        .modal-form select:focus {
            outline: none !important;
            border-color: #d4a017 !important;
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.15) !important;
            background-color: #ffffff !important;
        }
        
        /* Fondo de inputs */
        .modal-form input,
        .modal-form textarea,
        .modal-form select {
            background-color: #f8f9fa !important;
            color: #2c2c2c !important;
        }
        
        /* Color personalizado para texto púrpura */
        .text-purple {
            color: #8b5cf6 !important;
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
                    <li><a href="?c=Usuario&a=dashboardAdmin"><i class="fa-regular fa-folder"></i> Proyectos</a></li>
                    <li><a href="?c=Tarea&a=index"><i class="fa-regular fa-calendar-check"></i> Tareas</a></li>
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

        <!-- Contenido Principal -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-actions d-flex gap-3 align-items-center flex-wrap">
                    <button class="btn btn-warning text-dark fw-bold shadow-sm" id="add-project-btn">
                        <i class="fa-solid fa-plus me-2"></i>Nuevo Proyecto
                    </button>
                    
                    <select id="filter-category" class="form-select form-select-sm shadow-sm" style="width: auto; min-width: 200px;">
                        <option value="">📁 Categoría</option>
                        <option value="Software de gestión">Software de gestión (ERP, inventario, CRM, facturación)</option>
                        <option value="Tienda virtual">Tienda virtual / E-commerce</option>
                        <option value="Landing page">Landing page / Micrositio</option>
                        <option value="Portal corporativo">Portal corporativo / institucional</option>
                        <option value="Plataforma SaaS">Plataforma SaaS / interactiva</option>
                    </select>
                    
                    <select id="filter-progress" class="form-select form-select-sm shadow-sm" style="width: auto; min-width: 180px;">
                        <option value="">📊 Progreso</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Investigación">Investigación</option>
                        <option value="Diseño">Diseño</option>
                        <option value="En Desarrollo">En Desarrollo</option>
                        <option value="Implementación">Implementación</option>
                        <option value="Completado">Completado</option>
                    </select>
                    
                    <button class="btn btn-outline-dark btn-sm shadow-sm" id="report-btn">
                        <i class="fa-solid fa-chart-line me-2"></i>Reporte Rápido
                    </button>
                </div>
                <div class="search-wrapper mt-3">
                    <div class="input-group shadow-sm" style="max-width: 400px;">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" id="search-input" class="form-control" placeholder="Buscar proyecto...">
                    </div>
                </div>
            </header>

            <section class="projects-section">
                <h1 class="section-title mb-4 fw-bold">Control y Seguimiento de Proyectos en Curso</h1>
                <div class="projects-table-container shadow-sm rounded">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Proyecto</th>
                                <th>Categoría</th>
                                <th>Encargado</th>
                                <th>Progreso</th>
                                <th>Estado</th>
                                <th>Vence</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="projects-tbody">
                            <?php if (!empty($proyectos)): ?>
                                <?php foreach ($proyectos as $proyecto): ?>
                                    <tr data-category="<?= htmlspecialchars($proyecto['categoria'] ?? '') ?>" data-status="<?= htmlspecialchars($proyecto['estado']) ?>" class="align-middle">
                                        <td class="fw-semibold"><?= htmlspecialchars($proyecto['nombre']) ?></td>
                                        <td>
                                            <span class="text-dark"><?= htmlspecialchars($proyecto['categoria'] ?? 'Sin categoría') ?></span>
                                        </td>
                                        <td class="text-muted small"><?= $proyecto['encargados'] ?? '<span class="text-secondary">Sin asignar</span>' ?></td>
                                        <td style="min-width: 150px;">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-warning" role="progressbar" 
                                                     style="width: <?= $proyecto['porcentaje_avance'] ?>%;" 
                                                     aria-valuenow="<?= $proyecto['porcentaje_avance'] ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <span class="text-dark fw-semibold"><?= $proyecto['porcentaje_avance'] ?>%</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $estadoColor = match($proyecto['estado']) {
                                                'Completado' => 'text-success',
                                                'En Desarrollo' => 'text-primary',
                                                'Implementación' => 'text-info',
                                                'Diseño' => 'text-purple',
                                                'Investigación' => 'text-secondary',
                                                'Pendiente' => 'text-dark',
                                                'Vencido' => 'text-danger',
                                                default => 'text-secondary'
                                            };
                                            ?>
                                            <span class="fw-semibold <?= $estadoColor ?>"><?= htmlspecialchars($proyecto['estado']) ?></span>
                                        </td>
                                        <td><?= $proyecto['fecha_fin'] ? date('d/m/Y', strtotime($proyecto['fecha_fin'])) : '<span class="text-muted">Sin fecha</span>' ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary action-icon" data-action="view" data-id="<?= $proyecto['id_proyecto'] ?>" title="Ver detalles">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning action-icon" data-action="edit" data-id="<?= $proyecto['id_proyecto'] ?>" title="Editar">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger action-icon" data-action="delete" data-id="<?= $proyecto['id_proyecto'] ?>" title="Eliminar">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-regular fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                        <p class="mb-0">No hay proyectos registrados</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Contenedor de Modales -->
    <div class="modal-overlay" id="modal-overlay"></div>

    <!-- Modal: Crear Proyecto -->
    <div class="modal" id="create-modal">
        <div class="modal-header">
            <h2>Crear Proyecto</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="create-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="create-name">Nombre del Proyecto</label>
                        <input type="text" id="create-name" name="nombre" placeholder="Ingrese el nombre del proyecto" required>
                    </div>
                    <div class="form-group">
                        <label for="create-category">Categoría de proyecto</label>
                        <select id="create-category" name="categoria" required>
                            <option value="">Seleccione una categoría</option>
                            <option value="Software de gestión">Software de gestión (ERP, inventario, CRM, facturación)</option>
                            <option value="Tienda virtual">Tienda virtual / E-commerce</option>
                            <option value="Landing page">Landing page / Micrositio</option>
                            <option value="Portal corporativo">Portal corporativo / institucional</option>
                            <option value="Plataforma SaaS">Plataforma SaaS / interactiva</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="create-start-date">Fecha de inicio</label>
                        <input type="date" id="create-start-date" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="create-end-date">Fecha final</label>
                        <input type="date" id="create-end-date" name="fecha_fin">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="create-client">Ruc / Cliente</label>
                        <input type="text" id="create-client" name="cliente" placeholder="Ingrese su RUC / Nombre">
                    </div>
                    <div class="form-group">
                        <label for="create-resources">Recursos</label>
                        <input type="text" id="create-resources" name="recursos" placeholder="GitHub, GitLab">
                    </div>
                </div>
                <div class="form-group">
                    <label for="create-tech">Herramientas / Tecnologías</label>
                    <input type="text" id="create-tech" name="tecnologias" placeholder="Node.js, WordPress, PHP">
                </div>
                <div class="form-group">
                    <label for="create-description">Descripción / Objetivo Principal</label>
                    <textarea id="create-description" name="descripcion" rows="4" placeholder="Ingrese la descripción u objetivo principal"></textarea>
                </div>
                <button type="submit" class="btn btn-primary form-submit-btn">Guardar Proyecto</button>
            </form>
        </div>
    </div>

    <!-- Modal: Ver Información del Proyecto -->
    <div class="modal" id="view-modal">
        <div class="modal-header">
            <h2>Información General</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="view-grid">
                <!-- Información general -->
                <div class="view-section">
                    <div class="view-field">
                        <span class="view-label">Nombre del Proyecto:</span>
                        <span class="view-value" id="view-name">-</span>
                    </div>
                    <div class="view-field">
                        <span class="view-label">RUC / Cliente:</span>
                        <span class="view-value" id="view-cliente">-</span>
                    </div>
                    <div class="view-field">
                        <span class="view-label">Categoría:</span>
                        <span class="view-value" id="view-category">-</span>
                    </div>
                    <div class="view-field">
                        <span class="view-label">Estado:</span>
                        <span class="view-value" id="view-status">-</span>
                    </div>
                </div>
                
                <!-- Fechas y progreso -->
                <div class="view-section">
                    <div class="view-field">
                        <span class="view-label">Fecha de Inicio:</span>
                        <span class="view-value" id="view-start">-</span>
                    </div>
                    <div class="view-field">
                        <span class="view-label">Fecha Final:</span>
                        <span class="view-value" id="view-end">-</span>
                    </div>
                    <div class="view-field">
                        <span class="view-label">Recursos:</span>
                        <span class="view-value" id="view-recursos">-</span>
                    </div>
                    <div class="view-field">
                        <span class="view-label">Tecnologías:</span>
                        <span class="view-value" id="view-tecnologias">-</span>
                    </div>
                </div>
            </div>
            
            <div class="view-description">
                <h3>Descripción</h3>
                <p id="view-description">-</p>
            </div>
            
            <div class="project-section">
                <h3>Progreso del Proyecto</h3>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="view-progress-bar" style="width: 0%;"></div>
                    <span id="view-progress-text">0%</span>
                </div>
            </div>
            
            <div class="team-section">
                <h3>Equipo Asignado</h3>
                <div id="team-members-list" class="team-members">
                    <!-- Se llena dinámicamente con JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Modificar Proyecto -->
    <div class="modal" id="edit-modal">
        <div class="modal-header">
            <h2>Modificar Proyecto</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="edit-form">
                <input type="hidden" id="edit-id" name="id_proyecto">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-name">Nombre del Proyecto</label>
                        <input type="text" id="edit-name" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-category">Categoría de proyecto</label>
                        <select id="edit-category" name="categoria" required>
                            <option value="">Seleccione una categoría</option>
                            <option value="Software de gestión">Software de gestión (ERP, inventario, CRM, facturación)</option>
                            <option value="Tienda virtual">Tienda virtual / E-commerce</option>
                            <option value="Landing page">Landing page / Micrositio</option>
                            <option value="Portal corporativo">Portal corporativo / institucional</option>
                            <option value="Plataforma SaaS">Plataforma SaaS / interactiva</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-start-date">Fecha de inicio</label>
                        <input type="date" id="edit-start-date" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-end-date">Fecha final</label>
                        <input type="date" id="edit-end-date" name="fecha_fin">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-cliente">Ruc / Cliente</label>
                        <input type="text" id="edit-cliente" name="cliente" placeholder="Ej: Grupo Vicente Inversiones E.I.R.L.">
                    </div>
                    <div class="form-group">
                        <label for="edit-recursos">Recursos</label>
                        <input type="text" id="edit-recursos" name="recursos" placeholder="Ej: API Hub, gitlab">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-tecnologias">Herramientas / Tecnologías</label>
                    <input type="text" id="edit-tecnologias" name="tecnologias" placeholder="Ej: NodeJs, Wordpress, PHP">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-status">Estado</label>
                        <select id="edit-status" name="estado" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Investigación">Investigación</option>
                            <option value="Diseño">Diseño</option>
                            <option value="En Desarrollo">En Desarrollo</option>
                            <option value="Implementación">Implementación</option>
                            <option value="Completado">Completado</option>
                            <option value="Vencido">Vencido</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-progress">Progreso (%)</label>
                        <input type="number" id="edit-progress" name="porcentaje_avance" min="0" max="100" required readonly style="background-color: #e9ecef; cursor: not-allowed;">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-description">Descripción / Objetivo Principal</label>
                    <textarea id="edit-description" name="descripcion" rows="4" placeholder="Optimizar el módulo de ventas mediante la implementación de un dashboard dinámico..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary form-submit-btn">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal: Confirmación de Eliminación -->
    <div class="modal modal-small" id="delete-modal">
        <div class="modal-body text-center">
            <div class="alert-icon warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h2>¿Realmente quieres eliminar?</h2>
            <p>Esta acción no se puede deshacer.<br>Los datos eliminados se perderán permanentemente.</p>
            <div class="modal-actions">
                <button class="btn btn-outline" id="cancel-delete">Cancelar</button>
                <button class="btn btn-primary" id="confirm-delete">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Modal: Éxito -->
    <div class="modal modal-small" id="success-modal">
        <div class="modal-body text-center">
            <div class="alert-icon success">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2>¡Proyecto Creado!</h2>
            <p>Tu proyecto ha sido creado con éxito.</p>
        </div>
    </div>

    <!-- Modal: Error -->
    <div class="modal modal-small" id="error-modal">
        <div class="modal-body text-center">
            <div class="alert-icon error">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <h2 id="error-title">Error</h2>
            <p id="error-message">Ocurrió un error inesperado.</p>
        </div>
    </div>

    <!-- Modal: Reporte Rápido -->
    <div class="modal modal-report" id="report-modal">
        <div class="modal-header">
            <h2>Resumen</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p class="summary-subtitle">Estadísticas rápidas</p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Proyectos Totales</h3>
                    <p class="stat-number" id="stat-total">0</p>
                </div>
                <div class="stat-card">
                    <h3>Software de Gestión</h3>
                    <p class="stat-number" id="stat-frontend">0</p>
                </div>
                <div class="stat-card">
                    <h3>Tienda Virtual</h3>
                    <p class="stat-number" id="stat-backend">0</p>
                </div>
                <div class="stat-card">
                    <h3>Landing Page</h3>
                    <p class="stat-number" id="stat-infraestructura">0</p>
                </div>
            </div>
            
            <div class="alert-section">
                <h3 class="alert-title">Alerta</h3>
                <p class="alert-subtitle">Proyectos Vencidos</p>
                
                <div class="alert-list" id="report-alerts-list">
                    <?php 
                    $proyectosVencidos = array_filter($proyectos ?? [], function($p) {
                        return $p['estado'] === 'Vencido' || 
                               ($p['fecha_fin'] && strtotime($p['fecha_fin']) < time());
                    });
                    ?>
                    
                    <?php if (!empty($proyectosVencidos)): ?>
                        <?php foreach ($proyectosVencidos as $vencido): ?>
                            <div class="alert-item">
                                <h4><?= htmlspecialchars($vencido['nombre']) ?></h4>
                                <p><?= $vencido['estado'] ?></p>
                                <span class="alert-date">Fecha límite<br><?= date('d/m/Y', strtotime($vencido['fecha_fin'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-alerts">
                            <i class="fa-solid fa-check-circle"></i>
                            <p>No hay proyectos vencidos</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="<?php echo asset('js/dashboard.js'); ?>"></script>
</body>
</html>
