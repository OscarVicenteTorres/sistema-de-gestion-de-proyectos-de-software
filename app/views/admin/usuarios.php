<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Cuentas - Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/Admin/Base.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/Admin/Menu.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/Admin/Usuarios.css'); ?>">
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
                    <li><a href="?c=Usuario&a=index" class="active"><i class="fa-regular fa-user"></i> Cuentas</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <p class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Pedro Adriansen Flores') ?></p>
                    <p class="user-role">Administrador</p>
                </div>
                <a href="?c=Auth&a=logout" class="logout-icon" title="Cerrar sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">
            <div class="accounts-header">
                <h1 class="accounts-title">Gestor de Cuentas</h1>
            </div>

            <!-- Estadísticas y Botón Crear -->
            <div class="stats-grid">
                <div class="create-btn" onclick="openCreateModal()">
                    <i class="fa-solid fa-plus"></i>
                    <div class="create-text">Crear Nueva<br>Cuenta</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number" id="total-users"><?= count($usuarios ?? []) ?></div>
                    <div class="stats-label">Total de Usuarios</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number" id="active-users">
                        <?= count(array_filter($usuarios ?? [], fn($u) => ($u['activo'] ?? $u['estado'] ?? '') == 1 || ($u['estado'] ?? '') === 'Activo')) ?>
                    </div>
                    <div class="stats-label">Activos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number" id="blocked-users">
                        <?= count(array_filter($usuarios ?? [], fn($u) => ($u['activo'] ?? $u['estado'] ?? '') == 0 || ($u['estado'] ?? '') === 'Inactivo')) ?>
                    </div>
                    <div class="stats-label">Bloqueados</div>
                </div>
            </div>

            <!-- Secciones de Usuarios -->
            <div class="accounts-sections">
                <!-- Usuarios Activos -->
                <div class="activos-section">
                    <h2 class="section-header">Activos</h2>
                    <div id="active-users-list">
                        <?php if (!empty($usuarios)): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php if (($usuario['activo'] ?? $usuario['estado'] ?? '') == 1 || ($usuario['estado'] ?? '') === 'Activo'): ?>
                                    <div class="user-item">
                                        <div class="user-info">
                                            <h4><?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?></h4>
                                            <p><?= htmlspecialchars($usuario['rol_nombre'] ?? $usuario['rol'] ?? 'Desarrollador') ?> - <?= htmlspecialchars($usuario['tecnologias'] ?? 'Vue, css') ?></p>
                                        </div>
                                        <div class="user-actions">
                                            <button class="btn-icon btn-edit" onclick="editUser(<?= $usuario['id_usuario'] ?? $usuario['id'] ?>)" title="Editar">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                            <button class="btn-icon btn-view" onclick="viewUser(<?= $usuario['id_usuario'] ?? $usuario['id'] ?>)" title="Ver">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button class="btn-icon btn-block" onclick="blockUser(<?= $usuario['id_usuario'] ?? $usuario['id'] ?>)" title="Bloquear">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="user-item">
                                <div class="user-info">
                                    <h4>Pedro Joaquín Adriansen Flores</h4>
                                    <p>Frontend - Vue, css</p>
                                </div>
                                <div class="user-actions">
                                    <button class="btn-icon btn-edit" title="Editar">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <button class="btn-icon btn-view" title="Ver">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn-icon btn-block" title="Bloquear">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Usuarios Inactivos -->
                <div class="inactivos-section">
                    <h2 class="section-header">Inactivos</h2>
                    <div id="inactive-users-list">
                        <?php if (!empty($usuarios)): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php if (($usuario['activo'] ?? $usuario['estado'] ?? '') == 0 || ($usuario['estado'] ?? '') === 'Inactivo'): ?>
                                    <div class="user-item">
                                        <div class="user-info">
                                            <h4><?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?></h4>
                                            <p><?= htmlspecialchars($usuario['rol_nombre'] ?? $usuario['rol'] ?? 'Desarrollador') ?> - <?= htmlspecialchars($usuario['tecnologias'] ?? 'Vue, css') ?></p>
                                        </div>
                                        <div class="user-actions">
                                            <button class="btn-icon btn-activate" onclick="activateUser(<?= $usuario['id_usuario'] ?? $usuario['id'] ?>)" title="Activar">
                                                <i class="fa-solid fa-user-check"></i>
                                            </button>
                                            <button class="btn-icon btn-block" onclick="deleteUser(<?= $usuario['id_usuario'] ?? $usuario['id'] ?>)" title="Eliminar">
                                                <i class="fa-solid fa-user-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="user-item">
                                <div class="user-info">
                                    <h4>Pedro Joaquín Adriansen Flores</h4>
                                    <p>Frontend - Vue, css</p>
                                </div>
                                <div class="user-actions">
                                    <button class="btn-icon btn-activate" title="Activar">
                                        <i class="fa-solid fa-user-check"></i>
                                    </button>
                                    <button class="btn-icon btn-block" title="Eliminar">
                                        <i class="fa-solid fa-user-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Contenedor de Modales (reutilizado del dashboard) -->
    <div class="modal-overlay" id="modal-overlay"></div>

    <!-- Modal: Crear Nueva Cuenta -->
    <div class="modal" id="create-modal">
        <div class="modal-header">
            <h2>Crear Cuenta Nueva</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p style="text-align: center; color: #666; margin-bottom: 2rem;">Bienvenido a la empresa Grupo Vicente Inversiones E.I.R.L.</p>
            
            <form class="modal-form" id="create-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombres">Nombres *</label>
                        <input type="text" id="nombres" name="nombres" placeholder="Ingrese su nombres" 
                               required minlength="2" maxlength="50" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                               title="Solo letras y espacios (2-50 caracteres)">
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Apellidos *</label>
                        <input type="text" id="apellidos" name="apellidos" placeholder="Ingrese apellidos" 
                               required minlength="2" maxlength="50" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                               title="Solo letras y espacios (2-50 caracteres)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="documento">Nro documento *</label>
                        <input type="text" id="documento" name="documento" placeholder="Ej: 12345678" 
                               required minlength="8" maxlength="12" pattern="[0-9]+"
                               title="Solo números (8-12 dígitos)">
                    </div>
                    <div class="form-group">
                        <label for="tipo-documento">Tipo de documento *</label>
                        <select id="tipo-documento" name="tipo_documento" required>
                            <option value="">Seleccione tipo</option>
                            <option value="DNI">DNI</option>
                            <option value="CE">Carnet de Extranjería</option>
                            <option value="Pasaporte">Pasaporte</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="correo">Correo *</label>
               <input type="email" id="correo" name="correo" placeholder="usuario@ejemplo.com"
                   required maxlength="100"
                   title="Ingrese un correo válido">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
               <input type="tel" id="telefono" name="telefono" placeholder="Ej: 987654321"
                   minlength="7" maxlength="15"
                   title="Solo números y símbolos telefónicos">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="area-trabajo">Área de trabajo *</label>
                        <select id="area-trabajo" name="area_trabajo" required>
                            <option value="">Seleccione área</option>
                            <option value="Frontend">Frontend</option>
                            <option value="Backend">Backend</option>
                            <option value="Fullstack">Fullstack</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha-inicio">Fecha de inicio</label>
                        <input type="date" id="fecha-inicio" name="fecha_inicio" 
                               max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contrasena">Contraseña *</label>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 6 caracteres" 
                               required minlength="6" maxlength="50"
                               title="La contraseña debe tener al menos 6 caracteres">
                    </div>
                    <div class="form-group">
                        <label for="confirmar-contrasena">Confirmar Contraseña *</label>
                        <input type="password" id="confirmar-contrasena" name="confirmar_contrasena" 
                               placeholder="Repita la contraseña" required minlength="6" maxlength="50">
                    </div>
                </div>
                <div class="form-group">
                    <label for="tecnologias">Tecnologías (skills) *</label>
                    <input type="text" id="tecnologias" name="tecnologias" 
                           placeholder="React, Python, Java, WordPress" 
                           required minlength="2" maxlength="200"
                           title="Tecnologías separadas por comas">
                </div>
                <div class="form-group">
                    <label for="rol">Rol *</label>
                    <select id="rol" name="rol" required>
                        <option value="">Seleccione un rol</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Desarrollador">Desarrollador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary form-submit-btn">CREAR CUENTA</button>
            </form>
        </div>
    </div>

    <!-- Modal: Ver Detalles del Usuario -->
    <div class="modal" id="view-modal">
        <div class="modal-header">
            <h2>Detalles del Usuario</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="view-details">
                <div class="detail-row">
                    <strong>Nombre:</strong> <span id="view-nombre">Pedro Joaquín Adriansen Flores</span>
                </div>
                <div class="detail-row">
                    <strong>Documento:</strong> <span id="view-documento">60771494</span>
                </div>
                <div class="detail-row">
                    <strong>Correo:</strong> <span id="view-correo">pedroadriansen@gmail.com</span>
                </div>
                <div class="detail-row">
                    <strong>Teléfono:</strong> <span id="view-telefono">908061691</span>
                </div>
                <div class="detail-row">
                    <strong>Área:</strong> <span id="view-area">Frontend</span>
                </div>
                <div class="detail-row">
                    <strong>Contraseña:</strong> <span id="view-password">986547@af24</span>
                </div>
                <div class="detail-row">
                    <strong>Tecnologías:</strong> <span id="view-tecnologias">Vue, CSS</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Usuario -->
    <div class="modal" id="edit-modal">
        <div class="modal-header">
            <h2>Editar Usuario</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="edit-form">
                <input type="hidden" id="edit-id" name="id_usuario">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-nombres">Nombres *</label>
                        <input type="text" id="edit-nombres" name="nombres" 
                               required minlength="2" maxlength="50" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                               title="Solo letras y espacios (2-50 caracteres)">
                    </div>
                    <div class="form-group">
                        <label for="edit-apellidos">Apellidos *</label>
                        <input type="text" id="edit-apellidos" name="apellidos" 
                               required minlength="2" maxlength="50" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                               title="Solo letras y espacios (2-50 caracteres)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-documento">Nro documento *</label>
                        <input type="text" id="edit-documento" name="documento" 
                               required minlength="8" maxlength="12" pattern="[0-9]+"
                               title="Solo números (8-12 dígitos)">
                    </div>
                    <div class="form-group">
                        <label for="edit-tipo-documento">Tipo de documento *</label>
                        <select id="edit-tipo-documento" name="tipo_documento" required>
                            <option value="DNI">DNI</option>
                            <option value="CE">Carnet de Extranjería</option>
                            <option value="Pasaporte">Pasaporte</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-correo">Correo *</label>
               <input type="email" id="edit-correo" name="correo"
                   required maxlength="100"
                   title="Ingrese un correo válido">
                    </div>
                    <div class="form-group">
                        <label for="edit-telefono">Teléfono</label>
               <input type="tel" id="edit-telefono" name="telefono" 
                   minlength="7" maxlength="15"
                   title="Solo números y símbolos telefónicos">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-area-trabajo">Área de trabajo *</label>
                        <select id="edit-area-trabajo" name="area_trabajo" required>
                            <option value="Frontend">Frontend</option>
                            <option value="Backend">Backend</option>
                            <option value="Fullstack">Fullstack</option>
                            <!-- <option value="UI/UX">UI/UX</option> -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-fecha-inicio">Fecha de inicio</label>
                        <input type="date" id="edit-fecha-inicio" name="fecha_inicio" 
                               max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-contrasena">Nueva Contraseña (opcional)</label>
                        <input type="password" id="edit-contrasena" name="contrasena" 
                               placeholder="Dejar en blanco para mantener actual" 
                               minlength="6" maxlength="50"
                               title="La contraseña debe tener al menos 6 caracteres">
                    </div>
                    <div class="form-group">
                        <label for="edit-confirmar-contrasena">Confirmar Nueva Contraseña</label>
                        <input type="password" id="edit-confirmar-contrasena" name="confirmar_contrasena" 
                               placeholder="Confirmar nueva contraseña" minlength="6" maxlength="50">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-tecnologias">Tecnologías (skills) *</label>
                    <input type="text" id="edit-tecnologias" name="tecnologias" 
                           required minlength="2" maxlength="200"
                           title="Tecnologías separadas por comas">
                </div>
                <div class="form-group">
                    <label for="edit-rol">Rol *</label>
                    <select id="edit-rol" name="rol" required>
                        <option value="Administrador">Administrador</option>
                        <option value="Desarrollador">Desarrollador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary form-submit-btn">ACTUALIZAR USUARIO</button>
            </form>
        </div>
    </div>

    <!-- Modal: Confirmación de Bloqueo -->
    <div class="modal modal-small" id="block-confirmation-modal">
        <div class="modal-body text-center">
            <div class="alert-icon warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h2>¿Realmente quieres eliminar?</h2>
            <p>Esta acción no se puede deshacer.<br>Los datos eliminados se perderán permanentemente.</p>
            <div class="modal-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                <button class="btn btn-outline close-modal-btn" style="padding: 0.75rem 1.5rem; border: 1px solid #ccc; background: white; border-radius: 4px; cursor: pointer;">Cancelar</button>
                <button class="btn btn-primary" id="confirm-block-btn" style="padding: 0.75rem 1.5rem; background: #d4a017; color: white; border: none; border-radius: 4px; cursor: pointer;">confirmar</button>
            </div>
        </div>
    </div>

    <!-- Modal: Cuenta Bloqueada -->
    <div class="modal modal-small" id="block-success-modal">
        <div class="modal-body text-center">
            <div class="alert-icon success">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2>¡Cuenta Bloqueada!</h2>
            <p>La cuenta ha sido bloqueada con éxito.</p>
        </div>
    </div>

    <!-- Modal: Cuenta Activada -->
    <div class="modal modal-small" id="activate-success-modal">
        <div class="modal-body text-center">
            <div class="alert-icon success">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2>¡Cuenta Activada!</h2>
            <p>La cuenta ha sido Activada con éxito.</p>
        </div>
    </div>

    <!-- Modal: Éxito General -->
    <div class="modal modal-small" id="success-modal">
        <div class="modal-body text-center">
            <div class="alert-icon success">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2 id="success-title">¡Operación Exitosa!</h2>
            <p id="success-message">La operación se completó correctamente.</p>
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

    <!-- Bootstrap 5 Bundle JS (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo asset('js/usuarios.js'); ?>"></script>
</body>
</html>
