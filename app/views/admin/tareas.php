<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas - Administrador</title>
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

        <!-- Contenido Principal -->
        <main class="main-content">
            
            <!-- VISTA INICIAL: SELECCIONAR PROYECTO -->
            <div class="vista-inicial" id="vistaProyectos">
                <!-- Estadísticas de Proyectos -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-warning border-3">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold">Proyectos Registrados</h6>
                                <h2 class="text-warning fw-bold mb-0" id="statProyectosRegistrados">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning border-3">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold">Proyectos Completados</h6>
                                <h2 class="text-warning fw-bold mb-0" id="statProyectosCompletados">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning border-3">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold">Proyectos en Curso</h6>
                                <h2 class="text-warning fw-bold mb-0" id="statProyectosEnCurso">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning border-3">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold">Proyectos Vencidos</h6>
                                <h2 class="text-warning fw-bold mb-0" id="statProyectosVencidos">0</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <header class="main-header">
                    <h1 class="section-title mb-4 fw-bold">Selecciona un Proyecto para Gestionar Tareas</h1>
                    <div class="search-wrapper">
                        <div class="input-group shadow-sm" style="max-width: 400px;">
                            <span class="input-group-text bg-white">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" id="buscarProyecto" class="form-control" placeholder="Buscar proyecto...">
                        </div>
                    </div>
                </header>

                <section class="projects-section">
                    <div class="projects-table-container shadow-sm rounded">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th>Proyecto</th>
                                    <th>Categoría</th>
                                    <th>Progreso</th>
                                </tr>
                            </thead>
                            <tbody id="proyectosTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border text-warning" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- VISTA DE TAREAS: GESTIÓN DE TAREAS DEL PROYECTO -->
            <div class="vista-tareas" id="vistaTareas">
                <!-- Estadísticas de Tareas -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-warning border-3">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold">Tareas Totales</h6>
                                <h2 class="text-warning fw-bold mb-0" id="statTotal">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning border-3">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold">Completadas</h6>
                                <h2 class="text-warning fw-bold mb-0" id="statCompletada">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning border-3">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold">Justificación</h6>
                                <h2 class="text-warning fw-bold mb-0" id="statJustificaciones">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning border-3">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold">Vencidas</h6>
                                <h2 class="text-warning fw-bold mb-0" id="statVencidas">0</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proyecto Seleccionado -->
                <div class="proyecto-seleccionado" id="proyectoInfo">
                    <div>
                        <h3 id="nombreProyecto"></h3>
                        <small id="categoriaProyecto"></small>
                    </div>
                    <button class="btn btn-sm" onclick="volverAProyectos()">
                        <i class="fas fa-arrow-left me-2"></i> Volver a Proyectos
                    </button>
                </div>

                <header class="main-header">
    <div class="header-actions d-flex gap-3 align-items-center flex-wrap">
        <button class="btn btn-warning text-dark fw-bold shadow-sm" id="btnNuevaTarea">
    <i class="fas fa-plus me-2"></i> Crear Tarea
</button>
        <button class="btn btn-warning text-dark fw-bold shadow-sm" id="btnModificarTarea">
            <i class="fas fa-edit me-2"></i> Modificar Tarea
        </button>
        <button class="btn btn-warning text-dark fw-bold shadow-sm" id="btnEliminarTarea">
            <i class="fas fa-trash me-2"></i> Eliminar Tarea
        </button>
        <button class="btn btn-warning text-dark fw-bold shadow-sm" id="btnJustificaciones">
            <i class="fas fa-file-alt me-2"></i> Justificaciones
        </button>
    </div>
    <div class="search-wrapper mt-3">
        </div>
</header>

                <!-- Tabla de Tareas -->
                <section class="projects-section">
                    <div class="projects-table-container shadow-sm rounded">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th>Tarea</th>
                                    <th>desarrollador</th>
                                    <th>Progreso</th>
                                    <th>Estado</th>
                                    <th>Fecha Límite</th>
                                </tr>
                            </thead>
                            <tbody id="tareasTableBody">
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="spinner-border text-warning" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Overlay global para modales custom -->
    <div class="modal-overlay" id="modal-overlay"></div>

    <!-- Modal Crear/Editar Tarea (Custom) -->
    <div class="modal" id="modalTarea">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTareaTitle">Asignar Tareas</h2>
                <button type="button" class="close-modal-btn" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formTarea" class="modal-form">
                    <input type="hidden" id="tareaId">
                    <input type="hidden" id="tareaProyectoId">
                    
                    <div class="form-group">
                        <label for="tareaTitulo">Nombre de la Tarea</label>
                        <input type="text" id="tareaTitulo" required>
                    </div>
                    <div class="form-group">
                        <label for="tareaArea">Área Asignada</label>
                        <select id="tareaArea" required>
                            <option value="">Selecciona un Área</option>
                            <option value="Frontend">Frontend</option>
                            <option value="Backend">Backend</option>
                            <option value="Infraestructura">Infraestructura</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tareaUsuario">Desarrollador</label>
                        <select id="tareaUsuario" required>
                            <option value="">Selecciona un desarrollador</option>
                            
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tareaFechaInicio">Fecha de Inicio</label>
                            <input type="date" id="tareaFechaInicio">
                        </div>
                        <div class="form-group">
                            <label for="tareaFechaLimite">Fecha Límite</label>
                            <input type="date" id="tareaFechaLimite" required>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="tareaDescripcion">Descripción</label>
                        <textarea id="tareaDescripcion" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary form-submit-btn" id="btnGuardarTarea">Crear Tarea</button>
                </form>
            </div>
    </div>

    <!-- Modal Justificaciones -->
  <div class="modal modal-report" id="modalJustificaciones">
        <div class="modal-header">
            <h2 id="modalJustificacionesLabel">Justificaciones de Tareas</h2>
            <button type="button" class="close-modal-btn" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Contenedor para tarjetas de justificaciones (renderizado por JS) -->
            <div id="justificacionesContainer" class="d-flex flex-column gap-3">
                <div class="text-center text-muted py-4">Las justificaciones del proyecto seleccionado aparecerán aquí.</div>
            </div>
        </div>
  </div>
            <!-- Modal: Filtro / Detalle de Estadísticas (lista de tareas) -->
            <div class="modal" id="modalFiltroTareas">
                <div class="modal-header">
                    <h2 id="modalFiltroTareasLabel">Tareas</h2>
                    <button type="button" class="close-modal-btn" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formFiltroTareas" class="modal-form">
                        <div class="form-row">
                            <div class="form-group full-width">
                                <input type="text" id="filtroTitulo" placeholder="Buscar por nombre de tarea">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary form-submit-btn">Buscar</button>
                            </div>
                        </div>
                    </form>
                    <div id="resultadoFiltroTareas">
                        <div class="text-center py-4 text-muted">Use el formulario para filtrar tareas del proyecto seleccionado.</div>
                    </div>
                </div>
            </div>

<div class="modal modal-small" id="modalConfirmar">
    <div class="modal-body text-center">
        <div class="alert-icon warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h2 class="mb-2" id="modalConfirmarLabel">¿Realmente quieres eliminar?</h2>
        <p class="text-muted">Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary close-modal-btn">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnConfirmarEliminar">Confirmar</button>
        </div>
    </div>
</div>

<div class="modal modal-small" id="modalExito">
    <div class="modal-body text-center">
        <div class="alert-icon success">
            <i class="fa-solid fa-check"></i>
        </div>
        <h2 id="exitoTitulo">¡Operación Exitosa!</h2>
        <p id="exitoMensaje">La operación se completó correctamente.</p>
    </div>
</div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo asset('js/tareas.js'); ?>"></script>
</body>

</html>
