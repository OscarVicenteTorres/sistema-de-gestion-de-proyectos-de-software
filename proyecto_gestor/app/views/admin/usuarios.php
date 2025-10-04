<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Cuentas - Grupo Vicente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/dashboard-admin.css">
    <link rel="stylesheet" href="public/css/usuarios.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <h2 class="company-name">Grupo Vicente Inversiones E.I.R.L.</h2>
            <p class="company-subtitle">Gestor de Proyectos</p>
        </div>
        <nav class="nav-menu">
            <a href="?c=Proyecto&a=index" class="nav-link">
                <i class="fa-solid fa-folder"></i>
                <span>Proyectos</span>
            </a>
            <a href="?c=Tarea&a=index" class="nav-link">
                <i class="fa-solid fa-list-check"></i>
                <span>Tareas</span>
            </a>
            <a href="?c=Proyecto&a=exportar" class="nav-link">
                <i class="fa-solid fa-file-export"></i>
                <span>Exportar</span>
            </a>
            <a href="?c=Usuario&a=index" class="nav-link active">
                <i class="fa-solid fa-users"></i>
                <span>Cuentas</span>
            </a>
        </nav>
        <div class="admin-info">
            <p class="admin-name"><?php echo $_SESSION['nombre'] ?? 'Administrador'; ?> <?php echo $_SESSION['apellido'] ?? ''; ?></p>
            <p class="admin-role"><?php echo $_SESSION['rol_nombre'] ?? 'Administrador'; ?></p>
            <a href="?c=Auth&a=logout" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="page-header">
            <h1 class="page-title">Gestor de Cuentas</h1>
        </header>

        <!-- Stats Cards -->
        <section class="stats-grid">
            <button class="stat-card create-btn" onclick="openCreateModal()">
                <div class="stat-icon">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <h3>Crear Nueva</h3>
                <p>Cuenta</p>
            </button>

            <div class="stat-card">
                <h2 class="stat-number" id="total-usuarios">0</h2>
                <p class="stat-label">Total de Usuarios</p>
            </div>

            <div class="stat-card">
                <h2 class="stat-number" id="usuarios-activos">0</h2>
                <p class="stat-label">Activos</p>
            </div>

            <div class="stat-card">
                <h2 class="stat-number" id="usuarios-bloqueados">0</h2>
                <p class="stat-label">Bloqueados</p>
            </div>
        </section>

        <!-- Users Tables -->
        <section class="tables-container">
            <!-- Usuarios Activos -->
            <div class="users-section">
                <h2 class="section-title">Activos</h2>
                <div class="users-list" id="usuarios-activos-list">
                    <!-- Se llenarán dinámicamente -->
                </div>
            </div>

            <!-- Usuarios Inactivos -->
            <div class="users-section">
                <h2 class="section-title">Inactivos</h2>
                <div class="users-list" id="usuarios-inactivos-list">
                    <!-- Se llenarán dinámicamente -->
                </div>
            </div>
        </section>
    </main>

    <!-- Modal Crear Usuario -->
    <div class="modal-overlay" id="modal-create-overlay">
        <div class="modal modal-large" id="modal-create">
            <div class="modal-header">
                <h2 class="modal-title">Crear Cuenta Nueva</h2>
                <button class="modal-close" onclick="closeAllModals()">&times;</button>
            </div>
            <div class="modal-body">
                <p class="modal-subtitle">Grupo Vicente Inversiones E.I.R.L.</p>
                <form id="form-create-user">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombres</label>
                            <input type="text" name="nombres" placeholder="Ingrese su nombres" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos</label>
                            <input type="text" name="apellidos" placeholder="Ingrese apellidos" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nro documento</label>
                            <input type="text" name="documento" placeholder="123..." required maxlength="20">
                        </div>
                        <div class="form-group">
                            <label>Tipo de documento</label>
                            <select name="tipo_documento" required>
                                <option value="">tipo de documento</option>
                                <option value="DNI">DNI</option>
                                <option value="CE">CE</option>
                                <option value="Pasaporte">Pasaporte</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Correo</label>
                            <input type="email" name="correo" placeholder="@" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono" placeholder="Ingrese su numero de Teléfono" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Área de trabajo</label>
                            <select name="area_trabajo" required>
                                <option value="">Ingresar su área de trabajo</option>
                                <option value="Frontend">Frontend</option>
                                <option value="Backend">Backend</option>
                                <option value="Fullstack">Fullstack</option>
                                <option value="Diseño">Diseño</option>
                                <option value="Gerencia">Gerencia</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha de inicio</label>
                            <input type="date" name="fecha_inicio" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" name="contrasena" placeholder="Ingrese su Contraseña" required>
                        </div>
                        <div class="form-group">
                            <label>Confirmar Contraseña</label>
                            <input type="password" name="confirmar_contrasena" placeholder="Confirme su Contraseña" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tecnologías (skills)</label>
                        <textarea name="tecnologias" placeholder="React, Python, Java, Wooprdres" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn-submit">CREAR CUENTA</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ver Usuario -->
    <div class="modal-overlay" id="modal-view-overlay">
        <div class="modal modal-view" id="modal-view">
            <button class="modal-close" onclick="closeAllModals()">&times;</button>
            <div class="modal-body">
                <h2 class="modal-title">Detalles del Usuario</h2>
                <div class="user-details">
                    <p><strong>Nombre:</strong> <span id="view-nombre"></span></p>
                    <p><strong>Documento:</strong> <span id="view-documento"></span></p>
                    <p><strong>Correo:</strong> <span id="view-correo"></span></p>
                    <p><strong>Teléfono:</strong> <span id="view-telefono"></span></p>
                    <p><strong>Área:</strong> <span id="view-area"></span></p>
                    <p><strong>Contraseña:</strong> <span id="view-contrasena"></span></p>
                    <p><strong>Tecnologías:</strong> <span id="view-tecnologias"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Bloqueo/Activación -->
    <div class="modal-overlay" id="modal-confirm-overlay">
        <div class="modal modal-confirm" id="modal-confirm">
            <div class="modal-body text-center">
                <div class="confirm-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h2 id="confirm-title">¿Realmente quieres eliminar?</h2>
                <p id="confirm-message">Esta acción no se puede deshacer.<br>Los datos eliminados se perderán permanentemente.</p>
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeAllModals()">Cancelar</button>
                    <button class="btn-confirm" id="btn-confirm-action">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Éxito -->
    <div class="modal-overlay" id="success-modal-overlay">
        <div class="modal modal-small" id="success-modal">
            <div class="modal-body text-center">
                <div class="alert-icon success">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h2 id="success-title">¡Éxito!</h2>
                <p id="success-message">Operación completada exitosamente.</p>
            </div>
        </div>
    </div>

    <!-- Modal Error -->
    <div class="modal-overlay" id="error-modal-overlay">
        <div class="modal modal-small" id="error-modal">
            <div class="modal-body text-center">
                <div class="alert-icon error">
                    <i class="fa-solid fa-xmark"></i>
                </div>
                <h2 id="error-title">Error</h2>
                <p id="error-message">Ocurrió un error inesperado.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/usuarios.js"></script>
</body>
</html>
